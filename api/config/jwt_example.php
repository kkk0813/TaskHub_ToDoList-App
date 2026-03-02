<?php
// ============================================================
// TaskHub API - JWT Configuration
// ============================================================
//
// WHAT IS A JWT (JSON Web Token)?
// It's a long encoded string that contains information (like user ID).
// The server creates it when you log in, and you send it back 
// with every request to prove who you are.
//
// A JWT has 3 parts separated by dots:
//   eyJhbGci... . eyJ1c2Vy... . SflKxwRJ...
//   [HEADER]      [PAYLOAD]      [SIGNATURE]
//
// - HEADER: says which algorithm was used to sign it
// - PAYLOAD: contains your data (user_id, expiry time, etc.)
// - SIGNATURE: proves the token hasn't been tampered with
//              (created using the secret key below)
// ============================================================

// SECRET KEY - Used to sign and verify tokens
// In production, this should be a long random string stored in 
// an environment variable, NOT in the code. For learning, this is fine.
// If someone knows this key, they can forge tokens — so keep it secret!
define('JWT_SECRET', 'yoursecretkey');

// Token expires after 1 hour (3600 seconds)
// After this time, the user must log in again to get a new token.
define('JWT_EXPIRY', 3600);

// The "issuer" — identifies who created the token.
// This is just a label, but it helps when debugging.
define('JWT_ISSUER', 'taskhub-api');