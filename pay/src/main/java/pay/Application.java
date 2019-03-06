package pay;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@SpringBootApplication
@RestController
public class Application {

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


    public static void main(String[] args) {
        SpringApplication.run(Application.class, args);
    }

}
