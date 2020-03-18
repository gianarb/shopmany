package pay;

import com.sun.net.httpserver.HttpExchange;
import io.opentelemetry.OpenTelemetry;
import io.opentelemetry.context.propagation.HttpTextFormat;
import io.opentelemetry.trace.Span;
import io.opentelemetry.trace.SpanContext;
import io.opentelemetry.trace.Tracer;
import io.opentelemetry.trace.propagation.B3Propagator;
import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.net.URL;

@Component
public class TracerInterceptor
        extends HandlerInterceptorAdapter {
    // OTel API
    private Tracer tracer =
            OpenTelemetry.getTracerProvider().get("io.opentelemetry.pay.JaegerExample");

    // false -> we expect multi header
    B3Propagator b3Propagator = new B3Propagator(false);

    B3Propagator.Getter<HttpServletRequest> getter = new B3Propagator.Getter<HttpServletRequest>() {
        @javax.annotation.Nullable
        @Override
        public String get(HttpServletRequest carrier, String key) {
            return carrier.getHeader(key);
        }
    };
    private Span span;

    @Override
    public boolean preHandle(
            HttpServletRequest request,
            HttpServletResponse response,
            Object handler) throws IOException {
        URL url = new URL(request.getRequestURL().toString());
        SpanContext remoteCtx = b3Propagator.extract(request, getter);
        Span.Builder spanBuilder = tracer.spanBuilder(String.format("[%s] %d:%s", request.getMethod(), url.getPort(), url.getPath())).setSpanKind(Span.Kind.SERVER);
        if(remoteCtx != null){
            spanBuilder.setParent(remoteCtx);
        }
        span = spanBuilder.startSpan();
        span.setAttribute("http.method", request.getMethod());
        span.setAttribute("http.url", url.toString());
        return true;
    }
    @Override
    public void afterCompletion(
            HttpServletRequest request,
            HttpServletResponse response,
            Object handler,
            Exception ex) {
        span.setAttribute("http.status_code", response.getStatus());
        span.end();
    }
}
