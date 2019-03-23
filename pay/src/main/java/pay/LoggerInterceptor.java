package pay;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@Component
public class LoggerInterceptor
  extends HandlerInterceptorAdapter {
    private static final Logger logger = LoggerFactory.getLogger(Application.class);

    @Override
    public boolean preHandle(
      HttpServletRequest request,
      HttpServletResponse response,
      Object handler) {
        long startTime = System.currentTimeMillis();
        logger.info("[Start HTTP Request]: Path" + request.getRequestURL().toString()
				+ " StartTime=" + startTime);
        request.setAttribute("startTime", startTime);

        return true;
    }

    @Override
    public void afterCompletion(
      HttpServletRequest request,
      HttpServletResponse response,
      Object handler,
      Exception ex) {
        long startTime = (Long) request.getAttribute("startTime");
        logger.info("[End HTTP Request]: Path" + request.getRequestURL().toString()
				+ " EndTime=" + System.currentTimeMillis()
                + " TimeTaken="+ (System.currentTimeMillis() - startTime));
    }
}
