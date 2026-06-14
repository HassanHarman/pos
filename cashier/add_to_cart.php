<?php
	require_once('../main/auth.php');
	require_role(array('cashier'));
	include('../connect.php');

	$invoice = isset($_GET['invoice']) ? (string)$_GET['invoice'] : '';
	$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
	if ($invoice === '' || $product_id <= 0) {
		echo 'Invalid data';
		exit();
	}

	$p = $db->prepare("SELECT product_id, product_name FROM products WHERE product_id = :pid LIMIT 1");
	$p->execute(array(':pid' => $product_id));
	$prod = $p->fetch(PDO::FETCH_ASSOC);
	if (!$prod) {
		echo 'Product not found';
		exit();
	}
?>
<style>
    .add-to-cart-modal {
        width: 100%;
        max-width: 380px;
        padding: 4px;
        box-sizing: border-box;
    }
    .add-to-cart-modal h4 {
        margin: 0 0 0.5rem 0;
    }
    .add-to-cart-modal label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        margin: 0.6rem 0 0.25rem;
    }
    .add-to-cart-modal input,
    .add-to-cart-modal select {
        width: 100%;
        height: 36px;
        padding: 0.35rem 0.6rem;
        box-sizing: border-box;
    }
    .add-to-cart-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }
    .add-to-cart-actions .btn {
        flex: 1 1 140px;
    }
</style>

<div class="add-to-cart-modal">
    <form action="add_to_cart_save.php" method="post" id="addToCartForm">
        <input type="hidden" name="invoice" value="<?php echo htmlspecialchars($invoice); ?>" />
        <input type="hidden" name="product_id" value="<?php echo (int)$product_id; ?>" />
        <h4 style="margin-top:0;">Add to Cart</h4>
        <div><strong><?php echo htmlspecialchars($prod['product_name']); ?></strong></div>
        <br>
        <label>Size / Variant (optional)</label>
        <select name="variant_id" id="variant_id">
            <option value="">-- select size --</option>
        </select>
        <label>Quantity</label>
        <input type="number" name="qty" value="1" min="1" step="1" required />
        <div class="add-to-cart-actions">
            <button class="btn btn-success" type="submit" id="addToCartSubmit">Add to Cart</button>
            <button class="btn" type="button" onclick="jQuery(document).trigger('close.facebox');">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(function($){
        $('#variant_id').load('../main/get_variants.php?product_id=<?php echo (int)$product_id; ?>');

        var submitting = false;
        $('#addToCartForm').on('submit', function(){
            if (submitting) {
                return false;
            }
            submitting = true;
            $('#addToCartSubmit').prop('disabled', true);
            return true;
        });
    });
</script>