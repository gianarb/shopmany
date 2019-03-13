package pay;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@SpringBootApplication
@RestController
public class Application {

    // I whould like to replace this method to be a POST that accept a JSON body like this:
    // {"tot_price": 20.23, "customer_id": "3tgsze4fs5gs5"}
    // There is a MYSQL connected to this app that you can solve at the url paydb:3036 and I would like to store these info there.
    // you can start the app with docker compose doing `docker-compose up pay`. It starts both this app and the mysql database.
    // THANKS A LOT!
    @RequestMapping("/pay")
    public String home() {
        return "Hello Docker World";
    }

    public static void main(String[] args) {
        SpringApplication.run(Application.class, args);
    }

}
