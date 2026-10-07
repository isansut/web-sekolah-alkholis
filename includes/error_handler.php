<?php
/**
 * ERROR HANDLER FOR SMA AL-KHOLIS WEBSITE
 * Centralized error handling, logging, and error responses
 */

// Include necessary files
require_once __DIR__ . '/../config/database.php';

/**
 * Error handler configuration
 */
class ErrorConfig {
    const LOG_FILE = __DIR__ . '/../logs/error.log';
    const MAX_LOG_SIZE = 10 * 1024 * 1024; // 10MB
    const LOG_RETENTION_DAYS = 30;
    const DISPLAY_ERRORS = false; // Set to true for development
    const LOG_ERRORS = true;
    
    // Error types
    const ERROR_TYPES = [
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated'
    ];
}

/**
 * Main error handler class
 */
class ErrorHandler {
    private static $instance = null;
    private $logFile;
    private $displayErrors;
    private $logErrors;
    
    private function __construct() {
        $this->logFile = ErrorConfig::LOG_FILE;
        $this->displayErrors = ErrorConfig::DISPLAY_ERRORS;
        $this->logErrors = ErrorConfig::LOG_ERRORS;
        
        // Create logs directory if it doesn't exist
        $this->createLogDirectory();
        
        // Set error handlers
        $this->setErrorHandlers();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create log directory if it doesn't exist
     */
    private function createLogDirectory() {
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Set error handlers
     */
    private function setErrorHandlers() {
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        
        // Set exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Set shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);
        
        // Set error reporting level
        error_reporting(E_ALL);
        
        // Set display errors based on configuration
        ini_set('display_errors', $this->displayErrors ? '1' : '0');
        ini_set('log_errors', $this->logErrors ? '1' : '0');
    }
    
    /**
     * Handle PHP errors
     * @param int $errno Error level
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number where error occurred
     * @return bool
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        // Don't handle errors that are suppressed with @
        if (error_reporting() === 0) {
            return false;
        }
        
        $errorType = ErrorConfig::ERROR_TYPES[$errno] ?? 'Unknown Error';
        $errorMessage = "[{$errorType}] {$errstr} in {$errfile} on line {$errline}";
        
        // Log error
        if ($this->logErrors) {
            $this->logError($errorMessage, $errno);
        }
        
        // Display error if configured
        if ($this->displayErrors) {
            $this->displayError($errorMessage, $errorType);
        }
        
        // For fatal errors, don't continue execution
        if (in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $this->handleFatalError($errorMessage);
        }
        
        return true;
    }
    
    /**
     * Handle exceptions
     * @param Throwable $exception
     */
    public function handleException($exception) {
        $errorMessage = sprintf(
            "[Exception] %s: %s in %s on line %d\nStack trace:\n%s",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        // Log exception
        if ($this->logErrors) {
            $this->logError($errorMessage, E_ERROR);
        }
        
        // Display error
        if ($this->displayErrors) {
            $this->displayError($errorMessage, 'Exception');
        } else {
            $this->displayUserFriendlyError();
        }
    }
    
    /**
     * Handle shutdown (fatal errors)
     */
    public function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $errorMessage = sprintf(
                "[Fatal Error] %s in %s on line %d",
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            // Log fatal error
            if ($this->logErrors) {
                $this->logError($errorMessage, $error['type']);
            }
            
            // Display error
            if ($this->displayErrors) {
                $this->displayError($errorMessage, 'Fatal Error');
            } else {
                $this->displayUserFriendlyError();
            }
        }
    }
    
    /**
     * Handle fatal errors
     * @param string $errorMessage
     */
    private function handleFatalError($errorMessage) {
        // Log the error
        if ($this->logErrors) {
            $this->logError($errorMessage, E_ERROR);
        }
        
        // Display error or user-friendly message
        if ($this->displayErrors) {
            $this->displayError($errorMessage, 'Fatal Error');
        } else {
            $this->displayUserFriendlyError();
        }
        
        exit(1);
    }
    
    /**
     * Log error to file
     * @param string $message
     * @param int $level
     */
    private function logError($message, $level) {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = ErrorConfig::ERROR_TYPES[$level] ?? 'Unknown';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] [%s] [%s] [%s] [%s %s] %s\n",
            $timestamp,
            $levelName,
            $ip,
            $userAgent,
            $requestMethod,
            $requestUri,
            $message
        );
        
        // Check if log file needs rotation
        $this->rotateLogIfNeeded();
        
        // Write to log file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotate log file if it's too large
     */
    private function rotateLogIfNeeded() {
        if (file_exists($this->logFile) && filesize($this->logFile) > ErrorConfig::MAX_LOG_SIZE) {
            $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s');
            rename($this->logFile, $backupFile);
            
            // Compress old log file
            if (function_exists('gzopen')) {
                $compressedFile = $backupFile . '.gz';
                $this->compressFile($backupFile, $compressedFile);
                unlink($backupFile);
            }
        }
    }
    
    /**
     * Compress file using gzip
     * @param string $sourceFile
     * @param string $targetFile
     */
    private function compressFile($sourceFile, $targetFile) {
        $source = fopen($sourceFile, 'rb');
        $target = gzopen($targetFile, 'wb');
        
        while (!feof($source)) {
            gzwrite($target, fread($source, 4096));
        }
        
        fclose($source);
        gzclose($target);
    }
    
    /**
     * Clean up old log files
     */
    public function cleanupOldLogs() {
        $logDir = dirname($this->logFile);
        $cutoffTime = time() - (ErrorConfig::LOG_RETENTION_DAYS * 24 * 60 * 60);
        
        $files = glob($logDir . '/*.log.*');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * Display error for development
     * @param string $message
     * @param string $type
     */
    private function displayError($message, $type) {
        if (headers_sent()) {
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px; font-family: monospace;'>";
            echo "<h3 style='color: #c33; margin: 0 0 10px 0;'>{$type}</h3>";
            echo "<pre style='margin: 0; white-space: pre-wrap;'>{$message}</pre>";
            echo "</div>";
        } else {
            http_response_code(500);
            echo "<!DOCTYPE html><html><head><title>Error - {$type}</title></head><body>";
            echo "<div style='background: #fee; border: 1px solid #fcc; padding: 20px; margin: 20px; font-family: monospace;'>";
            echo "<h3 style='color: #c33; margin: 0 0 20px 0;'>{$type}</h3>";
            echo "<pre style='margin: 0; white-space: pre-wrap;'>{$message}</pre>";
            echo "</div></body></html>";
        }
    }
    
    /**
     * Display user-friendly error page
     */
    private function displayUserFriendlyError() {
        if (headers_sent()) {
            echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>";
            echo "<h2 style='color: #666;'>Maaf, terjadi kesalahan</h2>";
            echo "<p>Tim kami sedang bekerja untuk memperbaiki masalah ini.</p>";
            echo "<p><a href='javascript:history.back()'>Kembali ke halaman sebelumnya</a></p>";
            echo "</div>";
        } else {
            http_response_code(500);
            
            // Check if we can include the error page
            $errorPage = __DIR__ . '/../500.php';
            if (file_exists($errorPage)) {
                include $errorPage;
            } else {
                echo "<!DOCTYPE html><html><head><title>Error - Server Error</title></head><body>";
                echo "<div style='text-align: center; padding: 50px; font-family: Arial, sans-serif;'>";
                echo "<h2 style='color: #666;'>Maaf, terjadi kesalahan</h2>";
                echo "<p>Tim kami sedang bekerja untuk memperbaiki masalah ini.</p>";
                echo "<p><a href='javascript:history.back()'>Kembali ke halaman sebelumnya</a></p>";
                echo "</div></body></html>";
            }
        }
    }
    
    /**
     * Log database error
     * @param Exception $exception
     * @param string $context
     */
    public function logDatabaseError($exception, $context = '') {
        $message = sprintf(
            "[Database Error] %s in %s on line %d\nContext: %s\nSQL State: %s\nError Code: %d",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $context,
            $exception->getCode(),
            $exception->getCode()
        );
        
        $this->logError($message, E_USER_ERROR);
    }
    
    /**
     * Log security event
     * @param string $event
     * @param array $details
     */
    public function logSecurityEvent($event, $details = []) {
        $message = sprintf(
            "[Security Event] %s\nDetails: %s",
            $event,
            json_encode($details, JSON_UNESCAPED_UNICODE)
        );
        
        $this->logError($message, E_USER_WARNING);
    }
    
    /**
     * Log performance issue
     * @param string $operation
     * @param float $duration
     * @param array $context
     */
    public function logPerformanceIssue($operation, $duration, $context = []) {
        if ($duration > 1.0) { // Log if operation takes more than 1 second
            $message = sprintf(
                "[Performance Issue] %s took %.3f seconds\nContext: %s",
                $operation,
                $duration,
                json_encode($context, JSON_UNESCAPED_UNICODE)
            );
            
            $this->logError($message, E_USER_NOTICE);
        }
    }
    
    /**
     * Get error statistics
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getErrorStatistics($days = 7) {
        $stats = [
            'total_errors' => 0,
            'error_types' => [],
            'daily_errors' => [],
            'most_common_errors' => []
        ];
        
        if (!file_exists($this->logFile)) {
            return $stats;
        }
        
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[([^\]]+)\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                $errorType = $matches[2];
                
                if ($timestamp >= $cutoffTime) {
                    $stats['total_errors']++;
                    
                    // Count error types
                    $stats['error_types'][$errorType] = ($stats['error_types'][$errorType] ?? 0) + 1;
                    
                    // Count daily errors
                    $date = date('Y-m-d', $timestamp);
                    $stats['daily_errors'][$date] = ($stats['daily_errors'][$date] ?? 0) + 1;
                    
                    // Count most common errors
                    $errorMessage = preg_replace('/^\[[^\]]+\] \[[^\]]+\] \[[^\]]+\] \[[^\]]+\] \[[^\]]+\] /', '', $line);
                    $stats['most_common_errors'][$errorMessage] = ($stats['most_common_errors'][$errorMessage] ?? 0) + 1;
                }
            }
        }
        
        // Sort arrays
        arsort($stats['error_types']);
        arsort($stats['most_common_errors']);
        ksort($stats['daily_errors']);
        
        return $stats;
    }
}

/**
 * Convenience functions
 */

/**
 * Initialize error handler
 */
function initializeErrorHandler() {
    return ErrorHandler::getInstance();
}

/**
 * Log error manually
 * @param string $message
 * @param int $level
 */
function logError($message, $level = E_USER_ERROR) {
    $handler = ErrorHandler::getInstance();
    $handler->logError($message, $level);
}

/**
 * Log database error
 * @param Exception $exception
 * @param string $context
 */
function logDatabaseError($exception, $context = '') {
    $handler = ErrorHandler::getInstance();
    $handler->logDatabaseError($exception, $context);
}

/**
 * Log security event
 * @param string $event
 * @param array $details
 */
function logSecurityEvent($event, $details = []) {
    $handler = ErrorHandler::getInstance();
    $handler->logSecurityEvent($event, $details);
}

/**
 * Log performance issue
 * @param string $operation
 * @param float $duration
 * @param array $context
 */
function logPerformanceIssue($operation, $duration, $context = []) {
    $handler = ErrorHandler::getInstance();
    $handler->logPerformanceIssue($operation, $duration, $context);
}

/**
 * Get error statistics
 * @param int $days
 * @return array
 */
function getErrorStatistics($days = 7) {
    $handler = ErrorHandler::getInstance();
    return $handler->getErrorStatistics($days);
}

/**
 * Clean up old log files
 */
function cleanupErrorLogs() {
    $handler = ErrorHandler::getInstance();
    $handler->cleanupOldLogs();
}

/**
 * Handle AJAX error response
 * @param string $message
 * @param int $code
 */
function sendAjaxError($message, $code = 500) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'error_code' => $code
    ]);
    exit;
}

/**
 * Handle AJAX success response
 * @param array $data
 * @param string $message
 */
function sendAjaxSuccess($data = [], $message = 'Success') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Validate and sanitize input
 * @param mixed $input
 * @param string $type
 * @return mixed
 */
function validateInput($input, $type = 'string') {
    switch ($type) {
        case 'email':
            return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : false;
        case 'url':
            return filter_var($input, FILTER_VALIDATE_URL) ? $input : false;
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT);
        case 'boolean':
            return filter_var($input, FILTER_VALIDATE_BOOLEAN);
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Initialize error handler when this file is included
 */
initializeErrorHandler();
?> 