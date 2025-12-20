<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}
?>


<?php
include("header.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <script src="https://kit.fontawesome.com/144a91ca19.js" crossorigin="anonymous"></script>
  <title>Cart</title>
</head>
<body>

<section class="header-background">
  <div class="aboutus-pic">
    <h1>#Cart</h1>
  </div>
</section>

<div class="container mycart">
  <div class="row">

    <div class="col-lg-9">
      <table class="table">
        <thead class="text-center">
          <tr>
            <th>Serial No.</th>
            <th>Item Name</th>
            <th>Item Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th></th>
          </tr>
        </thead>
        <tbody class="text-center">
          <?php
          if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $value) {
              $sr = $key + 1;
              echo "
              <tr>
                <td>$sr</td>
                <td>$value[Item_Name]</td>
                <td>$value[Price]<input type='hidden' class='iprice' value='$value[Price]'></td>
                <td>
                  <form action='manage_cart.php' method='POST'>
                    <input class='text-center iquantity' name='Mod_Quantity' onchange='this.form.submit();' type='number' value='$value[Quantity]' min='1' max='10'>
                    <input type='hidden' name='Item_Name' value='$value[Item_Name]'>
                  </form>
                </td>
                <td class='itotal'></td>
                <td>
                  <form action='manage_cart.php' method='POST'>
                    <button name='Remove_Item' class='btn btn-sm btn-outline-danger'>Remove</button>
                    <input type='hidden' name='Item_Name' value='$value[Item_Name]'>
                  </form>
                </td>
              </tr>";
            }
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="col-lg-3">
      <div class="border bg-light rounded p-4">
        <h4>Grand Total:</h4>
        <h5 id="gtotal"></h5>
        <br>

        <?php
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

          if (!isset($_SESSION['user_id'])) {
        ?>

          <div class="alert alert-warning text-center">
            Please login to checkout
          </div>

          <a href="login.php" class="btn btn-primary btn-block btn-lg">
            <i class="fas fa-sign-in-alt"></i> Login to Checkout
          </a>

        <?php
          } else {
        ?>

          <div class="mb-3">
            <a href="#checkout-form" class="btn btn-success btn-block btn-lg">
              <i class="fas fa-shopping-cart"></i> Proceed to Checkout
            </a>
          </div>

          <hr>
          <h5 class="mb-3">Checkout Details</h5>

          <form action="purchase.php" method="POST" id="checkout-form" onsubmit="return handlePaymentMethod()">
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="full_name" class="form-control" value="<?php echo $_SESSION['username']; ?>" readonly>
            </div>

            <div class="form-group">
              <label>Phone Number</label>
              <input type="number" name="phone_no" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Address</label>
              <input type="text" name="address" class="form-control" required>
            </div>

            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="pay_mode" value="COD" checked>
              <label class="form-check-label">
                Cash On Delivery
              </label>
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="pay_mode" value="Khalti" id="khaltiPayment">
              <label class="form-check-label">
                Pay with Khalti
              </label>
            </div>

            <button class="btn btn-primary btn-block btn-lg" name="purchase">
              <i class="fas fa-check"></i> Complete Checkout
            </button>
          </form>

        <?php
          }
        }
        ?>
      </div>
    </div>

  </div>
</div>

<script>
var gt=0;
var iprice=document.getElementsByClassName('iprice');
var iquantity=document.getElementsByClassName('iquantity');
var itotal=document.getElementsByClassName('itotal');
var gtotal=document.getElementById('gtotal');

function subTotal() {
  gt=0;
  for(let i=0;i<iprice.length;i++){
    itotal[i].innerText = iprice[i].value * iquantity[i].value;
    gt += iprice[i].value * iquantity[i].value;
  }
  gtotal.innerText = gt;
}
subTotal();

function handlePaymentMethod(){
  if(document.getElementById('khaltiPayment').checked){
    document.getElementById('checkout-form').action='checkout.php';
  }
  return true;
}
</script>

</body>
</html>
