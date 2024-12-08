# NOWPayments Integration for WordPress

## Plugin Description

**Plugin Name**: NOWPayments Integration  
**Description**: A plugin for integrating the NOWPayments API with WordPress, allowing users to make payments for goods and services via NOWPayments.  
**Version**: 1.0  
**Author**: Dmitrij Shirokij  

## Plugin Functionality

The plugin provides the following features:

1. **Order Creation**: The plugin creates an orders table in the database to store payment information, including status, amount, and user details.

2. **Email Notifications**: After an order is created, the user receives a confirmation email using a customizable template.

3. **Integration with NOWPayments API**: The plugin handles requests to NOWPayments for payment creation and processes payment status notifications (IPN).

4. **Payment Simulation**: For administrators, the plugin can simulate successful payments to facilitate testing.

5. **Payment Status Handling**: The plugin updates the order status based on the response from NOWPayments and takes corresponding actions (e.g., post activation, sending notifications).

6. **Admin Order Page**: The plugin adds an "Orders" page to the WordPress admin menu where administrators can manage orders, view order details, and simulate payments.

## Code Overview
- **Order Table Creation**: Uses WordPress database functions to create and manage a custom order table upon plugin activation.
- **Payment Creation**: Implements AJAX functions to create payment requests and securely handle user data.
- **IPN Callback**: Registers a REST API endpoint to handle incoming payment notifications from NOWPayments.
- **Email Functionality**: Includes a function to send confirmations via email with a customizable template located within the plugin directory.
- **Security**: Ensures security through nonce verification and HMAC checks for callbacks, protecting against unauthorized access.
- **Admin Order Page Creation**: The function `np_add_admin_page()` creates an "Orders" page in the admin menu. On this page, the administrator can view orders.

The plugin is used as a module for the Pumb.Black theme.

## Screenshots

![Payment Page](https://github.com/DimaWide/wp-plugins/blob/main/nowpayments-integration/payment-page.jpg)  
![Orders Page](https://github.com/DimaWide/wp-plugins/blob/main/nowpayments-integration/np-orders-page.jpg)  
![Order Page Front](https://github.com/DimaWide/wp-plugins/blob/main/nowpayments-integration/order-page-front.jpg)
