/**
 * TaskHub API Helper
 * 
 * This file provides utility functions that all other JS files use
 * to communicate with the API. Instead of each file managing tokens
 * and headers individually, they call these shared functions.
 * 
 * WHAT IT DOES:
 * - Stores and retrieves the JWT token from localStorage
 * - Provides apiRequest() which automatically adds the token to every request
 * - Handles common errors (expired token, network issues)
 * 
 * HOW OTHER FILES USE IT:
 *   const data = await apiRequest('/api/tasks', 'GET');
 *   const data = await apiRequest('/api/tasks', 'POST', { title: 'New task', ... });
 */

// ============================================================
// TOKEN MANAGEMENT
// ============================================================

/**
 * Get the base URL for API requests.
 * This detects your project path automatically so the code works
 * regardless of where the project is installed.
 * 
 * Example result: "http://localhost:8080/project/ToDoListApp"
 */
function getBaseUrl() {
    // Get the current page URL and find where the project folders are
    const pathParts = window.location.pathname.split('/');
    // Find the index of the main project folder
    // We look for common folder names in your structure
    let basePath = '';
    for (let i = 0; i < pathParts.length; i++) {
        basePath += pathParts[i] + '/';
        // Stop when we find the app's root folder
        if (pathParts[i] === 'ToDoListApp') break;
    }
    return window.location.origin + basePath.replace(/\/+$/, '');
}

/**
 * Save the JWT token to localStorage.
 * localStorage persists even after closing the browser,
 * so the user stays "logged in" until the token expires.
 */
function saveToken(token) {
    localStorage.setItem('taskhub_token', token);
}

/**
 * Retrieve the stored JWT token.
 * Returns null if no token is stored (user not logged in).
 */
function getToken() {
    return localStorage.getItem('taskhub_token');
}

/**
 * Remove the stored token (used during logout).
 */
function clearToken() {
    localStorage.removeItem('taskhub_token');
}

/**
 * Check if the user has a stored token.
 */
function hasToken() {
    return getToken() !== null;
}


// ============================================================
// API REQUEST FUNCTION
// ============================================================

/**
 * Make an API request with automatic token handling.
 * 
 * This is the main function all other JS files use to talk to the API.
 * It automatically:
 * - Adds the Authorization header with the JWT token
 * - Sets the Content-Type to JSON
 * - Parses the JSON response
 * - Handles errors (expired token, network issues)
 * 
 * @param {string} endpoint - The API path (e.g., '/api/tasks')
 * @param {string} method - HTTP method ('GET', 'POST', 'PUT', 'DELETE')
 * @param {object|null} body - Data to send (for POST/PUT requests)
 * @param {boolean} isFormData - Set to true for file uploads
 * @returns {object} The parsed JSON response
 * 
 * EXAMPLES:
 *   // GET request (no body needed)
 *   const tasks = await apiRequest('/api/tasks', 'GET');
 * 
 *   // POST request with JSON body
 *   const result = await apiRequest('/api/tasks', 'POST', {
 *       title: 'New task',
 *       category: 'Assignment',
 *       due_date: '2025-04-15'
 *   });
 * 
 *   // File upload (FormData)
 *   const formData = new FormData();
 *   formData.append('profile_image', fileInput.files[0]);
 *   const result = await apiRequest('/api/profile/picture', 'POST', formData, true);
 */
async function apiRequest(endpoint, method = 'GET', body = null, isFormData = false) {
    const baseUrl = getBaseUrl();
    const url = baseUrl + endpoint;

    // Build the request options
    const options = {
        method: method,
        headers: {}
    };

    // Add the JWT token if we have one
    const token = getToken();
    if (token) {
        options.headers['Authorization'] = 'Bearer ' + token;
    }

    // Add the body for POST/PUT requests
    if (body && !isFormData) {
        // For JSON data: set content type and stringify the object
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    } else if (body && isFormData) {
        // For file uploads: don't set Content-Type — the browser 
        // automatically sets it with the correct boundary for FormData
        options.body = body;
    }

    try {
        const response = await fetch(url, options);
        const data = await response.json();

        // If we get a 401 (Unauthorized), the token is expired or invalid
        // Redirect to login page so the user can get a new token
        if (response.status === 401) {
            clearToken();
            // Only redirect if we're not already on the login page
            if (!window.location.pathname.includes('login')) {
                showAlert('Session expired. Please login again.', 'error');
                setTimeout(() => {
                    window.location.href = 'login-page.php';
                }, 2000);
            }
            return data;
        }

        return data;

    } catch (error) {
        console.error('API Request Error:', error);
        return {
            success: false,
            message: 'Network error. Please check your connection.'
        };
    }
}