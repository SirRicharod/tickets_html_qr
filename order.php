<?php
include 'includes/header.php'
?>

<div class="container">
  <div class="row">
    <div class="col">
      <h1>Order</h1>
      <img src="assets/images/sf.jpg" class="imgoffer">
      <?php if (isset($_SESSION['email'])) { ?>
        <h2>Hello <?php echo $_SESSION['name'] ?>,</h2>
        <p>Please order your tickets here! The tickets wil also be sent to you via this email adres: <b><?php echo $_SESSION['email'] ?></b></p>
        <p>Amount EUR 45 per ticket</p>
        <form action="orderprocess.php" method="post">
          <input type="number" class="form-control" name="amount">
          <p class="small">Tickets are not refundable; please use your common sense and to not sell them to the rest of the western world</p>
          <button class="btn btn-primary">Order</button>
        </form>
      <?php } else {
        echo "<h2>Acces denied</h2>";
        echo "<p>Please login</p>";
      }
      ?>

    </div>
  </div>
</div>
