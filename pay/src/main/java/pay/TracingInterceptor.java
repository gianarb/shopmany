package pay;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import io.opentracing.Span;
import io.opentracing.SpanContext;
import io.opentracing.Tracer;
import io.opentracing.propagation.TextMapExtractAdapter;
import io.opentracing.propagation.Format;
import io.jaegertracing.Configuration;
import io.jaegertracing.Configuration.ReporterConfiguration;
import io.jaegertracing.Configuration.SamplerConfiguration;
import io.jaegertracing.internal.JaegerTracer;
import java.util.Enumeration;
import java.util.HashMap;
import java.util.Map;

@Component
public class TracingInterceptor
  extends HandlerInterceptorAdapter {

    Tracer tracer = Configuration.fromEnv().getTracer();

    @Override
    public boolean preHandle(
      HttpServletRequest request,
      HttpServletResponse response,
      Object handler) {

        Map<String, String> headers = new HashMap<String, String>();
        Enumeration<String> headerNames = request.getHeaderNames();
        while (headerNames.hasMoreElements()) {
            String key = (String) headerNames.nextElement();
            String value = request.getHeader(key);
            headers.put(key, value);
        }

        String operationName = request.getMethod()+" "+request.getRequestURL().toString();
        Tracer.SpanBuilder spanBuilder = tracer.buildSpan(operationName);
        SpanContext parentSpan = tracer.extract(Format.Builtin.HTTP_HEADERS, new TextMapExtractAdapter(headers));
        if (parentSpan != null) {
            spanBuilder = tracer.buildSpan(operationName).asChildOf(parentSpan);
        }

        Span span = spanBuilder.start();

        span.setTag("path", request.getRequestURL().toString());
        span.setTag("method", request.getMethod());
        span.setTag("local_addr", request.getLocalAddr());
        span.setTag("content_type", request.getContentType());
        request.setAttribute("span", span);
        return true;
    }


    @Override
    public void afterCompletion(
      HttpServletRequest request,
      HttpServletResponse response,
      Object handler,
      Exception ex) {
        Span span = (Span)request.getAttribute("span");
        span.setTag("response_status",response.getStatus());
        if (ex != null) {
            span.setTag("error", true);
            span.setTag("error_message", ex.getMessage());
        }
        span.finish();
    }
}
