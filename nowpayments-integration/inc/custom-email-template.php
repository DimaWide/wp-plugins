<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Customize your email content
$subject = 'Your Order Confirmation';
$heading = 'Thank You for Your Order!';
$order_details = 'Here are your order details:';

// Define order details
$order_id = esc_html($payload['order_id']);
$package = esc_html(ucfirst($payload['package']));
$amount = esc_html($payload['price_amount']);
$currency = esc_html($payload['price_currency']);
$status = esc_html($payload['status'] ?? 'Pending');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($subject); ?></title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; color: #333;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f6f6f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td style="text-align: center; padding-bottom: 20px;">
                            <h1 style="color: #9029D9; font-size: 24px; margin: 0;"><?php echo esc_html($heading); ?></h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 20px;">
                            <p style="font-size: 16px; color: #333;"><?php echo esc_html($order_details); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" cellpadding="10" cellspacing="0" border="0" style="background-color: #f9f9f9; border-left: 5px solid #9029D9;">
                                <tr>
                                    <td style="font-size: 16px; color: #333;"><strong>Order ID:</strong> <?php echo $order_id; ?></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 16px; color: #333;"><strong>Package:</strong> <?php echo $package; ?></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 16px; color: #333;"><strong>Amount:</strong> <?php echo $amount . ' ' . strtoupper($currency); ?></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 16px; color: #333;"><strong>Status:</strong> <?php echo $status; ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px; text-align: center;">
                            <p style="font-size: 14px; color: #777;">If you have any questions, please <a href="mailto:support@yourdomain.com" style="color: #9029D9; text-decoration: none;">contact us</a>.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>

<?php
