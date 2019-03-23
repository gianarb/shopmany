package pay;

import org.springframework.web.servlet.config.annotation.InterceptorRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurerAdapter;
import org.springframework.stereotype.Component;

@Component
public class AppConfig extends WebMvcConfigurerAdapter  {

    @Override
    public void addInterceptors(InterceptorRegistry registry) {
       registry.addInterceptor(new TracingInterceptor());
       registry.addInterceptor(new LoggerInterceptor());
    }
}
