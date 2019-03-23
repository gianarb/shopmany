package pay;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import javax.servlet.http.HttpServletResponse;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@SpringBootApplication
@RestController
public class Application {
    private static final Logger logger = LoggerFactory.getLogger(Application.class);
    private PayRepository payRepository;

    public Application(PayRepository payRepository) {
        this.payRepository = payRepository;
    }

    @GetMapping("/pays")
    public ResponseEntity<?>  home() {
        return ResponseEntity.ok().body(payRepository.findAll());
    }

    @PostMapping("/pay")
    public ResponseEntity<?> home2(@RequestBody PayRequest payRequest) {
        PayEntity payEntity = new PayEntity(payRequest.getTot_price(), payRequest.getCustomer_id());
        payRepository.save(payEntity);
        return ResponseEntity.ok("Success");
    }

    @GetMapping("/health")
    @ResponseBody
    public HealthResponse health(HttpServletResponse response) {
        HealthResponse h = new HealthResponse();
        String status = "unhealthy";

        HealthCheck mysqlC = new HealthCheck();
        mysqlC.setName("mysql");
        try {
            payRepository.count();
            status = "healthy";
            mysqlC.setStatus("healthy");
        } catch (Exception e) {
            logger.error("Mysql healthcheck failed", e.getMessage());
            mysqlC.setStatus("unhealthy");
            mysqlC.setError(e.getMessage());
            response.setStatus(500);
        }
        h.setStatus(status);
        h.addHealthCheck(mysqlC);
        return h;
    }

    public static void main(String[] args) {
        SpringApplication.run(Application.class, args);
    }

}
