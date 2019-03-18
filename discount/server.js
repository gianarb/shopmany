var express = require("express");

var app = express();

var url = require('url');

var opentracing = require("opentracing");
var initJaegerTracer = require("jaeger-client").initTracer;
const MongoClient = require('mongodb').MongoClient;
const dbName = 'shopmany';
const client = new MongoClient('mongodb://discountdb:27017', { useNewUrlParser: true });
app.use(errorHandler)

const logger = require('pino')()
const expressPino = require('express-pino-logger')({
  logger: logger.child({"service": "httpd"})
})
app.use(expressPino)

function initTracer(serviceName) {
  var config = {
    serviceName: serviceName,
    sampler: {
      type: "const",
      param: 1,
    },
    reporter: {
      agentHost: "jaeger-workshop",
      logSpans: true,
    },
  };
  var options = {
    logger: {
      info: function logInfo(msg) {
        logger.info(msg, {
          "service": "tracer"
        })
      },
      error: function logError(msg) {
        logger.error(msg, {
          "service": "tracer"
        })
      },
    },
  };
  return initJaegerTracer(config, options);
}

const tracer = initTracer("discount");
opentracing.initGlobalTracer(tracer);
app.use(expressMiddleware({tracer: tracer}));

app.get("/health", function(req, res, next) {
  var resbody = {
    "status": "healthy",
    checks: [],
  };
  var resCode = 200;

  client.connect(function(err) {
    var mongoCheck = {
      "name": "mongo",
      "status": "healthy",
    };
    if (err != null) {
      req.log.warn(err.toString());
      mongoCheck.error = err.toString();
      mongoCheck.status = "unhealthy";
      resbody.status = "unhealthy"
      resCode = 500;
    }
    resbody.checks.push(mongoCheck);
    res.status(resCode).json(resbody)
  });
});

app.get("/discount", function(req, res, next) {
  client.connect(function(err) {
    db = client.db(dbName);
    const wireCtx = tracer.extract(opentracing.FORMAT_HTTP_HEADERS, req.headers);
    const pathname = url.parse(req.url).pathname;
    const span = tracer.startSpan("mongodb", {childOf: wireCtx});
    span.setTag("query", "db.items.find()");
    db.collection('discount').find({}).toArray(function(err, discounts) {
      if (err != null) {
        req.log.error(err.toString());
        span.setTag("error", true);
        span.finish();
        return next(err)
      }
      span.finish();
      var goodDiscount = null
      discounts.forEach(function (s) {
        if (s.itemID+"" == req.query.itemid) {
          goodDiscount = s
        }
      });
      if (goodDiscount != null) {
        res.json({"discount": goodDiscount})
      } else {
        req.log.warn("discount not found");
        res.status(404).json({ error: 'Discount not found' });
      }
      return
    })
  });
});

app.use(function(req, res, next) {
  req.log.warn("route not found");
  return res.status(404).json({error: "route not found"});
});

function errorHandler(err, req, res, next) {
  req.log.error(err.toString(), {
    error_status: err.status
  });
  var st = err.status
  if (st == 0 || st == null) {
    st = 500;
  }
  res.status(err.status);
  res.json({ error: err })
}

app.listen(3000, () => {
  logger.info("Server running on port 3000");
});

function expressMiddleware(options = {}) {
  const tracer = options.tracer || opentracing.globalTracer();

  return (req, res, next) => {
    const wireCtx = tracer.extract(opentracing.FORMAT_HTTP_HEADERS, req.headers);
    const pathname = url.parse(req.url).pathname;
    const span = tracer.startSpan(pathname, {childOf: wireCtx});
    span.logEvent("request_received");

    span.setTag("http.method", req.method);
    span.setTag("span.kind", "server");
    span.setTag("http.url", req.url);

    const responseHeaders = {};
    tracer.inject(span, opentracing.FORMAT_TEXT_MAP, responseHeaders);
    Object.keys(responseHeaders).forEach(key => res.setHeader(key, responseHeaders[key]));

    Object.assign(req, {span});

    const finishSpan = () => {
      span.logEvent("request_finished");
      const opName = (req.route && req.route.path) || pathname;
      span.setOperationName(opName);
      span.setTag("http.status_code", res.statusCode);
      if (res.statusCode >= 500) {
        span.setTag("error", true);
        span.setTag("sampling.priority", 1);
      }
      span.finish();
    };
    res.on('close', finishSpan);
    res.on('finish', finishSpan);

    next();
  };
}
