package pay;

import java.util.*;

public class HealthResponse {
    private String status;

    private List<HealthCheck> checks;

    public HealthResponse () {
        this.checks = new ArrayList<HealthCheck>();
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }

    public void addHealthCheck(HealthCheck h) {
        this.checks.add(h);
    }

    public List<HealthCheck> getChecks() {
        return checks;
    }
}
