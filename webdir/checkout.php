<?php

//In case you want to show errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Require the configuration before any PHP code as the configuration controls error reporting
require ('./includes/config.inc.php');
// The config file also starts the session

// If the user isn't logged in, redirect them
redirect_invalid_user();

// We need database connection
require (MYSQL);

// Listing the ads & first purchases needs the _SRV config
require_once ('./includes/config_srv.inc.php');
require_once (MYSQL_SRV);

// Test some necessary variables, otherwise redirect
if ((isset($_POST['stripeToken'])) && (preg_match('/[^a-zA-Z0-9_]$/i', $_POST['stripeToken']))) {$IP = get_ip_addr(); script_kiddy('sk_41', '_POST stripeToken', $_POST['stripeToken'], $IP);}
if ((isset($_POST['freeToken'])) && (preg_match('/[^a-zA-Z0-9_]$/i', $_POST['freeToken']))) {$IP = get_ip_addr(); script_kiddy('sk_42', '_POST freeToken', $_POST['freeToken'], $IP);}
if ((isset($_POST['stripeToken'])) && (isset($_SESSION['validAd'])) && (isset($_SESSION['user_id']))) {
  // Get the payment token ID submitted by the form
  $token = preg_replace("/[^A-Za-z0-9_]/","", $_POST['stripeToken']);
  // Set the User ID variable
  $userid = $_SESSION['user_id'];
  // Set the payment method
  $payment_method = "stripe";
} elseif ((isset($_POST['freeToken'])) && (isset($_SESSION['validAd'])) && (isset($_SESSION['user_id'])) && ((isset($_SESSION['purchase_key_id'])) || (isset($_SESSION['user_is_admin']))) ) {
  // Get the payment token ID submitted by the form
  $token = "FREE_".time();
  // Set the User ID variable
  $userid = $_SESSION['user_id'];
  // Set the payment method
  $payment_method = "free";
  // Freekey?
	// Kill our key
  if (!$_SESSION['user_is_admin']) {
    $key_id = $_SESSION['purchase_key_id'];
  	$q = "UPDATE freekeys SET purchase_useable='dead' WHERE id='$key_id'";
  	$r = mysqli_query ($dbc, $q);
  	if (mysqli_affected_rows($dbc) != 1) {
  		sql_error($q, 'dbc', "sqle_121");
  		header("Location: index.php");
  		exit(); // Quit the script
  	}
  }
	// Set join_rank?
	// See if the user was already in the join list
	$q = "SELECT userid FROM join_rank WHERE userid='$userid'";
	$r = mysqli_query ($srv_dbc, $q);
	$rows = mysqli_num_rows($r);
	if ($rows == 0) { // Join the user if not
		$qt = "INSERT INTO join_rank (userid) VALUES ('$userid')";
		$rt = mysqli_query ($srv_dbc, $qt);
		if (mysqli_affected_rows($srv_dbc) == 0) { // If didn't run okay
			sql_error($qt, 'srv_dbc', "sqle_122");
		}
	}
  // Join rank?
  if ($_SESSION['joined'] != 'joined') {
  	// Get the new join_rank
  	$q = "SELECT id FROM join_rank WHERE userid='$userid'";
  	$r = mysqli_query($srv_dbc, $q);
  	$row = mysqli_fetch_array($r, MYSQLI_NUM);
  	$join_rank = "$row[0]";
  	// Add the join_rank to the users table
  	$q = "UPDATE users SET join_rank='$join_rank' WHERE id='$userid'";
  	$r = mysqli_query ($dbc, $q);
  	if (!$r) { // If it ran okay
  		sql_error($q, 'dbc', "sqle_123");
  	} // Added user's join_rank
  }
  // Done with our reg_key
  unset($_SESSION['reg_key_id']);
} elseif ((isset($_SESSION['validAd'])) && (isset($_SESSION['user_id']))) {
  $userid = $_SESSION['user_id'];
  // Set the User ID variable
  $valid = $_SESSION['validAd'];
  header("Location: new_ad_cart.php");
  exit(); // Quit the script
} else {
  header("Location: new_ad.php");
  exit(); // Quit the script
}

// Process the payment

  // Stripe form process
  if (!isset($_POST['freeToken'])) {
    require ('includes/stripe.inc.php');
    require_once ('stripe-php/init.php');
    \Stripe\Stripe::setApiKey("$stripeSkey");
    $badadPaymentGateway = "Stripe";
  } else {
    $badadPaymentGateway = "badAd (Free Purchases)";
  }

  // Referral values?
  if (isset($_SESSION['refUserID'])) { $refUserID = $_SESSION['refUserID']; }
  if (isset($_SESSION['rSlug'])) { $rSlug = $_SESSION['rSlug']; }
  // REFERRED

  // Set the New Ad variables
  require_once ('./includes/ad_values_set.inc.php');

  // join_rank & receipt_email
  $q = "SELECT join_rank, email, confirmed_email FROM users WHERE id='$userid'";
  $r = mysqli_query ($dbc, $q);
  $row = mysqli_fetch_array($r, MYSQLI_NUM);
  $join_rank = "$row[0]";
  $new_ad_email = "$row[1]";
  $new_ad_confirmed_email = "$row[2]";
  // Choose the email
  if (($new_ad_email == $new_ad_confirmed_email) || ($new_ad_confirmed_email == "Unconfirmed")) {
    $new_ad_receipt_email = $new_ad_email;
  } else {
    $new_ad_receipt_email = $new_ad_confirmed_email;
  }

  // Existing ad_comment?
  if (isset($new_ad_comment)) {
    $new_ad_comment .= 'Checkout';
  } else {
    $new_ad_comment .= 'Checkout';
  }
  // Beta boost?
  if (isset($_SESSION['beta_boost'])) {
    $new_ad_comment .= ' Betaboost-'.$_SESSION['beta_boost'];
  }
  if ((isset($_SESSION['creditsUsing'])) && ($_SESSION['creditsUsing'] > 0)) {
    $new_ad_comment .= ' Credits-'.$_SESSION['creditsUsing'];
  }

  include ('./includes/create_pending_ad.inc.php');
  if ((isset($_SESSION['ad_pending_to_sql'])) && ($_SESSION['ad_pending_to_sql'] == true)) {

    // Retrieve the New Ad ID
    $q = "SELECT id FROM ads WHERE transaction_id='$token'";
    $r = mysqli_query ($dbc, $q);
    $row = mysqli_fetch_array($r, MYSQLI_NUM);
    $adID = "$row[0]";

    // Convert the price to an integer
    $intAdPrice = preg_replace('/[^0-9]/', '', $adPricePaying);

    // Payment method

          // Stripe
          if ($payment_method == "stripe") {

                // Catch any Stripe errors
                try {
                  // Run the charge
                  $charge = \Stripe\Charge::create([
                      'amount' => $intAdPrice,
                      'currency' => 'usd',
                      'description' => "badAd: $new_ad_nickname",
                      'source' => $token,
                      'metadata' => ['order_id' => $adID]
                ]);
                    $success = 1;
                } catch(Stripe_CardError $e) {
                  $error1 = $e->getMessage();
                } catch (Stripe_InvalidRequestError $e) {
                  // Invalid parameters were supplied to Stripe's API
                  $error2 = $e->getMessage();
                } catch (Stripe_AuthenticationError $e) {
                  // Authentication with Stripe's API failed
                  $error3 = $e->getMessage();
                } catch (Stripe_ApiConnectionError $e) {
                  // Network communication with Stripe failed
                  $error4 = $e->getMessage();
                } catch (Stripe_Error $e) {
                  // Display a very generic error to the user, and maybe send
                  // yourself an email
                  $error5 = $e->getMessage();
                } catch (Exception $e) {
                  // Something else happened, completely unrelated to Stripe
                  $error6 = $e->getMessage();
                }

                if (!isset($success)) {
                    if (isset($error1)) {$_SESSION['error1'] = $error1;}
                    elseif (isset($error2)) {$_SESSION['error2'] = $error2;}
                    elseif (isset($error3)) {$_SESSION['error3'] = $error3;}
                    elseif (isset($error4)) {$_SESSION['error4'] = $error4;}
                    elseif (isset($error5)) {$_SESSION['error5'] = $error5;}
                    elseif (isset($error6)) {$_SESSION['error6'] = $error6;}
                    else {$_SESSION['error7'] = "An unknown error occured in the transaction. Please try again.";}
                    $_SESSION['stripe_error'] = true;
                    header("Location: new_ad_cart.php");
                    exit();
                }


                // Parse the response from Stripe here
                // Change the response into an array
                $chargeArray = $charge->__toArray(true);
                // Set PDTclass variables from the response array
                $stripe_payment_success = $chargeArray['paid'];
                $stripePaymentID = $chargeArray['id'];
                $stripeReceiptURL = $chargeArray['receipt_url'];
                $stripePaymentAmt = $chargeArray['amount'];
                //put the decimal back in place
                $stripePaymentAmt = $stripePaymentAmt/100;
                if (strpos($stripePaymentAmt, ".") == false) { $stripePaymentAmt = $stripePaymentAmt.'.00'; }
                $stripeDescription = $chargeArray['description'];
            // Free
          } elseif ($payment_method == "free") {
              $stripe_payment_success = true;
              $stripePaymentID = "FREE_".time();
              $stripeReceiptURL = "FREE";
              $stripePaymentAmt = "0.00";
              $stripeDescription = "badAd: $new_ad_nickname";
            }

    // Success
    if ($stripe_payment_success == true) {
      // Update the New Ad status in the database
      include ('./includes/create_paid_ad.inc.php');

      if ((isset($_SESSION['ad_paid_to_sql'])) && ($_SESSION['ad_paid_to_sql'] == true)) { // If SQL ran okay

        // First purchase?
        if ($join_rank == NULL) {
          // See if the user was already in the join list
          $q = "SELECT userid FROM join_rank WHERE userid='$userid'";
          $r = mysqli_query ($srv_dbc, $q);
          $rows = mysqli_num_rows($r);
          if ($rows == 0) { // Join the user if not
            $qt = "INSERT INTO join_rank (userid) VALUES ('$userid')";
            $rt = mysqli_query ($srv_dbc, $qt);
            if (mysqli_affected_rows($srv_dbc) == 0) { // If didn't run okay
              sql_error($qt, 'srv_dbc', "sqle_87");
            }
          }
          // Get the new join_rank
          $q = "SELECT id FROM join_rank WHERE userid='$userid'";
          $r = mysqli_query($srv_dbc, $q);
          $row = mysqli_fetch_array($r, MYSQLI_NUM);
          $join_rank = "$row[0]";
          // Add the join_rank to the users table
          $q = "UPDATE users SET join_rank='$join_rank' WHERE id='$userid'";
          $r = mysqli_query ($dbc, $q);
          if (mysqli_affected_rows($dbc) == 1) { // If it ran okay
            // Process the email confirmation
    				include ('includes/confirm_email.inc.php');
          } else {
            sql_error($q, 'dbc', "sqle_88");
          } // Added user's join_rank
        }

        // Deduct any used credits
        if (isset($_SESSION['creditsUsing'])) {
          include ('./includes/credits_charged.inc.php');
        }

        // Add to the tally
        $qt = "INSERT INTO current_cycle (ad_id, price) VALUES ('$adID', '$adPricePaying')";
        $rt = mysqli_query ($dbc, $qt);

        // List the ad
        // $adID must be set!
        include ('./includes/list_ad.inc.php');

        // Get the expiration date
        $q = "SELECT date_expires FROM ads WHERE id='$adID'";
        $r = mysqli_query($dbc, $q);
        $row = mysqli_fetch_array($r, MYSQLI_NUM);
        $expirationDate = "$row[0]";

        // Unset the ad session
        unset ($_SESSION['validAd']);


// Begin rendering the page

        // Include the header file
        $page_title = "Checkout :: $siteTitle";
        include ('./includes/header.html');

        // Referred?
        include ('./includes/referral_cred_grant.inc.php');
        // REFERRED

        // Freekey?
        if (isset($_SESSION['purchase_key_id'])) {
        	echo '<p class="note_blue">Free key mode, $0/wk</p>';
          unset($_SESSION['purchase_key_id']);
        }

        // Breadcrumb
        echo "<p>&larr; Go to <a title=\"Order History\" href=\"order_history.php\">Order History</a></p>";

        // Calculate the number of days for prettiness
        $days = $new_ad_weeks_duration * 7;

        // Pretty weeks
        if ($new_ad_weekslong == 1) {
        	$pretty_new_ad_weekslong = "$new_ad_weeks_duration week";
        } elseif ($new_ad_weekslong > 1) {
        	$pretty_new_ad_weekslong = "$new_ad_weeks_duration weeks";
        }

        // Prepare some message variables
        $itemized_receipt_statement = (isset($_SESSION['itemized_receipt_statement'])) ? $_SESSION['itemized_receipt_statement'] : '';
        if ($new_ad_pod_listing == 'pod') {
          $adType = 'podcast & business';
        } elseif ($new_ad_biz_listing == 'biz') {
          $adType = 'business';
        } elseif ($new_ad_biz_listing == 'non') {
          $adType = 'normal';
        }

        // Create messages
        $ad_preview = "<p class=\"badad_ad\">$adContent</p>";
        $receipt_view = "<p>Amount charged:<br /><b class\"receipt_dollad_amount\">$$stripePaymentAmt USD</b><br />Invoice No: $adID<br />Ad Nickname: $new_ad_nickname<br />Category: $categoryName<br />Subcategory: $subcatName<br />Role: $roleName<br />Type: $adType<br />Runs for: $pretty_new_ad_weekslong ($days days)<br />Until: $expirationDate UTC<br /><i>Prices may include promotions and are no guarantee of future pricing.</i></p>";
        $receipt_view .= "<p>$itemized_receipt_statement</p>";
        $podcast_content = (($new_ad_pod_listing == 'pod') && (isset($new_ad_content_pdcst)) && ($new_ad_content_pdcst != NULL)) ? "<p><b>Proposed podcast ad manuscript:</b><br /><i>$new_ad_content_pdcst</i><br /><br />The above manuscript will be reviewed by our team. Until then, enjoy the text portion of your ad free, on us at no expense! If our review team finds a problem with your manuscript, we will notify you of our suggested changes, asking you to approve them, and we will pause your free text ad until we can approve your manuscript. Once approved, your ad will begin to run for the time period that came with your purchase, for both your text and podcast ads.</p>" : '';
        $ad_preview_and_disclaimer = "<p>Contact URL: $new_ad_contactURL<br />Ad Content:<br /><hr />$ad_preview<hr /></p>$podcast_content<p>As you agreed, all sales are final and no refunds are given under any circumstances. If you wish to pull your add before the expiration date, such as in the event your information changes or an item sells, you may \"kill\" your ad in your <a title=\"View your order history\" href=\"order_history.php\">Order History</a>.</p>";

        // Send the order confirmation email
        $payment_link_notice = ($stripeReceiptURL == 'FREE') ? "" : "<p>You may also <a href=\"$stripeReceiptURL\">view your online receipt from Stripe</a></p>";
        $canned_email = "payment_receipt"; // Slug from the "pantry" table to select the canned email
        $subject_suffix = " - $siteTitle"; // Appends the canned email Subject
        $payload_content = "{$receipt_view} {$ad_preview_and_disclaimer} {$payment_link_notice}"; // Middle of the Body, after the canned email and before the salutation
        $footer_link_content = ""; // After the salutation and before the unsubscribe footer
        include ('./includes/sendusrmail.inc.php');

        // Success message
        echo ($stripeReceiptURL == 'FREE') ? "<div class=\"checkout_success_receipt\" style=\"text-align: center;\">This is a free ad. There is no payment receipt.</div>" : "<div class=\"checkout_success_receipt\" style=\"text-align: center;\"><h4>Payment successful!</h4>$receipt_view<p><a class=\"recept_link\" target=\"_blank\" title=\"View receipt on $badadPaymentGateway\" href=\"$stripeReceiptURL\" >View your $badadPaymentGateway receipt here</a></p>$ad_preview_and_disclaimer</div>";

        // Credits used?
        if (isset($newCreditsMessage)) {
          echo $newCreditsMessage;
        }

        // Remove the session for the payment confirmation
        unset($_SESSION['ad_paid_to_sql']);

        // Unset the _SESSION ad values
        include ('./includes/ad_values_unset.inc.php');

      } else { // create_paid_ad.inc
        echo "It shouldn't be possible to be here.";
      }

     // Declined
   } else {
     echo "It shouldn't be possible to be here.";
   } // End declined

   // Remove the session for the SQL confirmation
   unset($_SESSION['ad_pending_to_sql']);

 } else { // create_pending_ad.inc
    echo "It shouldn't be possible to be here.";
 }
// Include the HTML footer
include ('./includes/footer.html');

// Clear the receipt statement
unset ($_SESSION['itemized_receipt_statement']);

// Clear any rerun _SESSION
if (isset($_SESSION['rerun_ad'])) {
  unset($_SESSION['rerun_ad']);
}

// Clear any freekey
if (isset($_SESSION['purchase_key_id'])) {
  unset($_SESSION['purchase_key_id']);
}

?>
