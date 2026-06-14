<?php
// ============================================================
// INVOICE PREVIEW PAGE - MODERN UI/UX
// Fully responsive with print functionality
// Preserves ALL original functionality
// Currency: UGX (Ugandan Shilling)
// ============================================================

require_once('auth.php');
require_role(array('cashier','manager','owner'));

function createRandomPassword() {
    $chars = "003232303232023232023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '';
    while ($i <= 7) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}
$finalcode = 'RS-' . createRandomPassword();

function formatMoney($number, $fractional=false) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (true) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}

function formatUGX($amount) {
    return 'UGX ' . formatMoney($amount, true);
}

$invoice = isset($_GET['invoice']) ? $_GET['invoice'] : '';
include('../connect.php');

$role = function_exists('current_role') ? current_role() : strtolower(trim((string)$_SESSION['SESS_LAST_NAME']));
$viewerUserId = isset($_SESSION['SESS_MEMBER_ID']) ? $_SESSION['SESS_MEMBER_ID'] : null;

if ($role === 'cashier') {
    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number = :userid AND user_id = :uid");
    $result->execute(array(':userid' => $invoice, ':uid' => $viewerUserId));
} else {
    $result = $db->prepare("SELECT * FROM sales WHERE invoice_number = :userid");
    $result->execute(array(':userid' => $invoice));
}

$cname = '';
$invoice = '';
$date = '';
$cash = '';
$cashier = '';
$pt = '';
$am = '';
$subtotal = null;
$vat_amount = null;
$total_amount = null;
$amount_paid = null;
$change_amount = null;
$amount = null;

for($i=0; $row = $result->fetch(); $i++){
    $cname = $row['name'];
    $invoice = $row['invoice_number'];
    $date = $row['date'];
    $cash = $row['due_date'];
    $cashier = $row['cashier'];
    $pt = $row['type'];
    $am = $row['amount'];
    $subtotal = isset($row['subtotal']) ? $row['subtotal'] : null;
    $vat_amount = isset($row['vat_amount']) ? $row['vat_amount'] : null;
    $total_amount = isset($row['total_amount']) ? $row['total_amount'] : null;
    $amount_paid = isset($row['amount_paid']) ? $row['amount_paid'] : null;
    $change_amount = isset($row['change_amount']) ? $row['change_amount'] : null;
    if($pt == 'cash'){
        $cash = $row['due_date'];
        $amount = $cash - $am;
    }
}

if (!isset($invoice) || $invoice === '' || !isset($pt)) {
    header('location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Invoice Preview | POS System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f8 0%, #e8ecf4 100%);
            min-height: 100vh;
            padding: 1rem;
        }

        /* Main Container */
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .btn-back {
            background: #f1f5f9;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 40px;
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .btn-print {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-print:hover {
            background: linear-gradient(135deg, #4338ca, #4f46e5);
            transform: translateY(-2px);
        }

        /* Invoice Card */
        .invoice-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Invoice Header */
        .invoice-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .store-name {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.3rem;
        }
        .store-address {
            font-size: 0.75rem;
            color: #64748b;
        }
        .receipt-title {
            font-size: 1rem;
            font-weight: 600;
            color: #4f46e5;
            margin-top: 0.5rem;
        }

        /* Invoice Info Grid */
        .info-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .info-box {
            background: #f8fafc;
            padding: 0.8rem 1rem;
            border-radius: 16px;
            flex: 1;
            min-width: 180px;
        }
        .info-label {
            font-size: 0.65rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        .info-value {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .items-table th {
            text-align: left;
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.7rem;
        }
        .items-table td {
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.75rem;
        }
        .items-table tfoot td {
            padding: 0.8rem;
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #e2e8f0;
        }

        /* Totals Section */
        .totals {
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e2e8f0;
        }
        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            margin-bottom: 0.5rem;
        }
        .total-label {
            font-weight: 600;
            color: #475569;
        }
        .total-value {
            font-weight: 700;
            min-width: 120px;
            text-align: right;
        }
        .grand-total {
            font-size: 1.1rem;
            color: #4f46e5;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px dashed #e2e8f0;
            font-size: 0.7rem;
            color: #94a3b8;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .action-bar {
                display: none !important;
            }
            .invoice-card {
                box-shadow: none;
                padding: 0;
            }
            .btn-print, .btn-back {
                display: none;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .invoice-card {
                padding: 1rem;
            }
            .store-name {
                font-size: 1.3rem;
            }
            .info-grid {
                flex-direction: column;
            }
            .info-box {
                min-width: auto;
            }
            .items-table {
                font-size: 0.65rem;
            }
            .total-row {
                flex-direction: column;
                align-items: flex-end;
                gap: 0.3rem;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">
    
    <!-- Action Bar -->
    <div class="action-bar" data-aos="fade-down">
        <a href="sales.php?id=cash&invoice=<?php echo $finalcode; ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Sales
        </a>
        <a href="javascript:window.print();" class="btn-print">
            <i class="fas fa-print"></i> Print Invoice
        </a>
    </div>
    
    <!-- Invoice Card -->
    <div class="invoice-card" data-aos="fade-up" data-aos-delay="100">
        
        <!-- Header -->
        <div class="invoice-header">
            <div class="store-name">Real Sisters POS</div>
            <div class="store-address">Kampala, Uganda</div>
            <div class="receipt-title">SALES RECEIPT</div>
        </div>
        
        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-label"><i class="fas fa-receipt"></i> Invoice Number</div>
                <div class="info-value"><?php echo htmlspecialchars($invoice); ?></div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-calendar"></i> Date</div>
                <div class="info-value"><?php echo date('F d, Y', strtotime($date)); ?></div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-user"></i> Cashier</div>
                <div class="info-value"><?php echo htmlspecialchars($cashier); ?></div>
            </div>
            <div class="info-box">
                <div class="info-label"><i class="fas fa-user"></i> Customer</div>
                <div class="info-value"><?php echo htmlspecialchars($cname); ?></div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th><i class="fas fa-barcode"></i> Product Code</th>
                    <th><i class="fas fa-box"></i> Product Name</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Price (UGX)</th>
                    <th style="text-align:right;">Amount (UGX)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $db->prepare("SELECT * FROM sales_order WHERE invoice = :userid");
                $result->execute(array(':userid' => $invoice));
                while($row = $result->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td style="text-align:center;"><?php echo $row['qty']; ?></td>
                    <td style="text-align:right;"><?php echo formatUGX($row['price']); ?></td>
                    <td style="text-align:right;"><?php echo formatUGX($row['amount']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Subtotal:</strong></td>
                    <td style="text-align:right;">
                        <?php
                        $sdsd = $_GET['invoice'];
                        $resultas = $db->prepare("SELECT SUM(amount) as total FROM sales_order WHERE invoice = :a");
                        $resultas->execute(array(':a' => $sdsd));
                        $rowas = $resultas->fetch();
                        $subtotalAmount = $rowas['total'];
                        echo formatUGX($subtotalAmount);
                        ?>
                    </td>
                </tr>
                
                <?php if ($subtotal !== null && $vat_amount !== null && $total_amount !== null): ?>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>VAT (18%):</strong></td>
                    <td style="text-align:right;"><?php echo formatUGX($vat_amount); ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Total with VAT:</strong></td>
                    <td style="text-align:right;"><?php echo formatUGX($total_amount); ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if($pt == 'cash'): ?>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Cash Tendered:</strong></td>
                    <td style="text-align:right;">
                        <?php
                        if ($amount_paid !== null) {
                            echo formatUGX($amount_paid);
                        } else {
                            echo formatUGX($cash);
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Change:</strong></td>
                    <td style="text-align:right;">
                        <?php
                        if ($change_amount !== null) {
                            echo formatUGX($change_amount);
                        } else {
                            echo formatUGX($amount);
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if($pt == 'credit'): ?>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Due Date:</strong></td>
                    <td style="text-align:right;"><?php echo date('M d, Y', strtotime($cash)); ?></td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <i class="fas fa-heart" style="color: #ef4444;"></i> Thank you for shopping with us!<br>
            Please keep this receipt for warranty purposes.
        </div>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script type="text/javascript">
    // Initialize AOS
    AOS.init({
        duration: 400,
        once: true
    });
    
    // Force scroll to top
    if('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
</script>

</body>
</html>