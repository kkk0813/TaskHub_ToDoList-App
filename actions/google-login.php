require_once '../includes/config.php';
$clientId     = GOOGLE_CLIENT_ID;
$clientSecret = GOOGLE_CLIENT_SECRET;
define('GOOGLE_REDIRECT_URI', 'http://localhost/project/toDoListApp/google-callback.php');

// google-login.php - Button click handler
session_start();

// Generate a random state parameter for security
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Create the Google authorization URL
$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?';
$authUrl .= 'client_id=' . GOOGLE_CLIENT_ID;
$authUrl .= '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URI);
$authUrl .= '&response_type=code';
$authUrl .= '&scope=email+profile'; 
$authUrl .= '&state=' . $state;

// Redirect to Google
header('Location: ' . $authUrl);
exit;

// google-callback.php - Where Google redirects after login
session_start();

// Verify state parameter to prevent CSRF attacks
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    die('Invalid state parameter');
}

// Exchange the authorization code for tokens
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Set up cURL request to exchange code for tokens
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_POST, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokens = json_decode($response, true);
    
    // Use the access token to get user info
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
    $ch = curl_init($userInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokens['access_token']]);
    
    $userInfo = curl_exec($ch);
    curl_close($ch);
    
    $user = json_decode($userInfo, true);
    
    // Now you have user info like $user['email'], $user['name'], etc.
    // Check if this Google user already exists in your database
    // If yes, log them in. If not, create a new account
    
    // Redirect to dashboard
    header('Location: ../public/dashboard-page.php');
    exit;
}