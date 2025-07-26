<?php

// This file contains the database access information
// This file establishes a connection to MySQL and selects the database
// This file defines a function for making data safe to use in queries
// This file defines a function for hashing passwords

// Set the database access information as constants
DEFINE ('AGG_NAME', 'badadfeeddb');
DEFINE ('AGG_USER', 'badadfeeddb');
DEFINE ('AGG_PASSWORD', 'badadfeeddbpassword');
DEFINE ('AGG_HOST', 'localhost');

// Make the connection
$agg_dbc = mysqli_connect (AGG_HOST, AGG_USER, AGG_PASSWORD, AGG_NAME);

// Set the character set
mysqli_set_charset($agg_dbc, 'utf8');

// Function for escaping and trimming form data
// Takes one argument: the data to be treated (string)
// Returns the treated data (string)
function escape_data_agg ($data) {

	global $agg_dbc; // Database connection

	// Apply trim() and mysqli_real_escape_string()
	return mysqli_real_escape_string ($agg_dbc, trim ($data));

} // End of the escape_data() function

// This function returns the hashed version of a password
// It takes the user's password as its one argument
// It returns a binary version of the password, already escaped to use in a query
function get_password_hash_agg ($password) {

	// Need the database connection
	global $agg_dbc;

	// Return the escaped password
	return mysqli_real_escape_string ($agg_dbc, hash_hmac('sha256', $password, 'c#haRl891', true));

} // End of get_password_hash() function
