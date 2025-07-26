<?php
echo "<form action=\"$pformaction\" method=\"post\" id=\"payment-form\">
	<div class=\"form-row\">
	  <label for=\"card-element\">
	   <p class=\"pay_with\">Pay with credit or debit card</p>
	  </label>
	  <div id=\"card-element\">
	    <!-- A Stripe Element will be inserted here. -->
	  </div>

	  <!-- Used to display form errors. -->
	  <div id=\"card-errors\" role=\"alert\"></div>
	</div>

	<br /><button class =\"formbutton_green\">Submit payment</button><br /><br />
</form>

<script src=\"js/stripe.js\"></script>";
