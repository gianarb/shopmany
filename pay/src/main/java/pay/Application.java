package pay;

import io.grpc.ManagedChannel;
import io.grpc.ManagedChannelBuilder;
import io.opentelemetry.exporters.jaeger.JaegerGrpcSpanExporter;
import io.opentelemetry.exporters.logging.LoggingSpanExporter;
import io.opentelemetry.sdk.OpenTelemetrySdk;
import io.opentelemetry.sdk.trace.export.SimpleSpansProcessor;
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
        // Create a channel towards Jaeger end point
        ManagedChannel jaegerChannel = ManagedChannelBuilder.forAddress("jaeger", 14250).usePlaintext().build();
        // Export traces to Jaeger

        JaegerGrpcSpanExporter jaegerExporter = JaegerGrpcSpanExporter.newBuilder()
                .setServiceName("pay")
                .setChannel(jaegerChannel)
                .setDeadlineMs(30000)
                .build();
        // Export also to the console
        LoggingSpanExporter loggingExporter = new LoggingSpanExporter();
        OpenTelemetrySdk.getTracerProvider().addSpanProcessor(SimpleSpansProcessor.newBuilder(loggingExporter).build());
        // Set to process the spans by the Jaeger Exporter
        OpenTelemetrySdk.getTracerProvider()
                .addSpanProcessor(SimpleSpansProcessor.newBuilder(jaegerExporter).build());
        SpringApplication.run(Application.class, args);
    }

}

