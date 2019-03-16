package pay;

import javax.persistence.*;

@Entity
@Table(name="item")
public class PayEntity {

    @Id
    @GeneratedValue(strategy = GenerationType.AUTO)
    private long id;

    private double totPrice;

    private String customerId;

    public PayEntity() {
    }

    public PayEntity(double totPrice, String customerId) {
        this.totPrice = totPrice;
        this.customerId = customerId;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public double getTotPrice() {
        return totPrice;
    }

    public void setTotPrice(double totPrice) {
        this.totPrice = totPrice;
    }

    public String getCustomerId() {
        return customerId;
    }

    public void setCustomerId(String customerId) {
        this.customerId = customerId;
    }
}
