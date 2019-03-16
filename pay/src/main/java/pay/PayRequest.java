package pay;

public class PayRequest {
    private double tot_price;
    private String customer_id;

    public PayRequest() {

    }

    public double getTot_price() {
        return tot_price;
    }

    public void setTot_price(double tot_price) {
        this.tot_price = tot_price;
    }

    public String getCustomer_id() {
        return customer_id;
    }

    public void setCustomer_id(String customer_id) {
        this.customer_id = customer_id;
    }
}
