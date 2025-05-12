<?php 

// Uncomment the line below to prevent direct access when used inside CodeIgniter 3
// defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mailcheck - Common Email Pattern Validation Library
 * 
 * Specialized in validating standard email formats (e.g., Gmail, Outlook) and conventional domain terminations (.com, .com.br).
 * Designed for use cases requiring strict validation of typical email patterns, including:
 * - Web form validation
 * - Data cleaning pipelines
 * - Database filtering
 * - Allowlist-based security systems
 * 
 * Key Advantages:
 * - More restrictive than PHP's filter_var() for common use cases
 * - Configurable allowlists for providers/domain endings
 * - Dual-mode operation (static and instance methods)
 * - Comprehensive sanitization utilities
 * - Batch processing capabilities
 * 
 * @copyright 2025 Pedro Rigolin
 * @license MIT (See LICENSE file)
 * @version 1.0.0
 * @link https://github.com/pedrohrigolin/Mailcheck-PHP
 */
class Mailcheck{

    /**
     * Default validation mode (standard format check)
     */
    private const MODE_DEFAULT = 0;

    /**
     * Termination validation mode (requires specific domain endings)
     */
    private const MODE_TERMINATION = 1;
    
    /**
     * Provider validation mode (only allows whitelisted email providers)
     */
    private const MODE_PROVIDERS = 2;

    /**
     * Available validation modes mapping
     */
    private const MODES = [
        'MODE_DEFAULT' => 0,
        'MODE_TERMINATION' => 1,
        'MODE_PROVIDERS' => 2
    ];

    /**
     * Whitelisted email providers (e.g., gmail.com, outlook.com)
     * @var array
     */
    private static $ALLOWED_PROVIDERS = [
        'gmail.com',
        'outlook.com',
        'outlook.com.br',
        'hotmail.com',
        'hotmail.com.br',
        'live.com',
        'live.com.br',
        'yahoo.com',
        'yahoo.com.br',
        'terra.com',
        'terra.com.br',
        'icloud.com',
        'uol.com.br',
        'myyahoo.com',
        'myyahoo.com.br'
    ];

    /**
     * Compiled regex pattern for provider validation
     * @var string
     */
    private static $REGEX_PROVIDERS = '';

    /**
     * Allowed domain terminations with validation patterns
     * @var array
     */
    private static $ALLOWED_TERMINATIONS = [

        '.com' => [
            'final' => '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+(\.com)$/um',
            'middle' => '/\.com\./'
        ],

        '.com.br' => [
            'final' => '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+(\.com\.br)$/um',
            'middle' => '/\.com\.br\./'
        ],

        '.br' => [
            'final' => '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+(\.br)$/um',
            'middle' => '/\.br\./'
        ]

    ];

    /**
     * Initialization status flag
     * @var bool
     */
    private static $INITIALIZED = FALSE;

    /**
     * Class construct.
     * @return	void
     */
    public function __construct(){
        self::init();
    }

    /**
     * Normalizes email(s) format
     * @param string|array $email Input email(s)
     * @param bool $fixDots Whether to fix multiple dots
     * @return string|array Sanitized result(s)
     * 
     * @example
     * Mailcheck::sanitize(' user,,name@domain.com '); 
     * // Returns "user.name@domain.com"
     */
    public static function sanitize( string|array $email, bool $fixDots = false ): string|array {

        $sanitize = function( string|array $email ): string|array {

            $email = preg_replace(
                $fixDots ? 
                    ['/\s+/', '/[,;]/', '/^\.+|\.+$/m', '/\.{2,}/'] : 
                    ['/\s+/', '/[,;]/', '/^\.+|\.+$/m'],
                $fixDots ? 
                    ['', '.', '', '.'] : 
                    ['', '.', ''],
                $email
            );

            return $email;

        };

        if( gettype($email) === 'string' ){
            $email = $sanitize($email);
        }
        else{

            foreach($email as $key => $mail){
                if( gettype($mail) !== 'string' ) $email[$key] = (string) $mail;
            }

            unset($key, $mail);

            $email = $sanitize($email);

        }

        unset($sanitize, $fixDots);

        return $email;

    }

    /**
     * Normalizes array values (strings only, optional regex escaping)
     * @param array $array Input array to normalize
     * @param bool $preg_quote Whether to escape regex special chars
     * @return array Normalized array
     */
    private static function normalize_array( array $array, bool $preg_quote = false ): array {

        $newArray = [];

        foreach($array as $value){
            if( gettype($value) !== 'string' ) $value = (string) $value;
            $value = preg_replace('/\s+/', '', $value);
            if($preg_quote) $value = preg_quote($value);
            $newArray[] = $value;
        }

        return $newArray;

    }

    /**
     * Prepares termination strings for validation patterns
     * @param string|array $termination Termination(s) to normalize
     * @return array Normalized patterns [final, middle]
     */
    private static function normalize_termination( string|array $termination ): array {

        if( gettype($termination) === 'array' ) $termination = self::normalize_array($termination);

        $termination = preg_replace(
            ['/\s+/', '/[,;]/', '/^\.+|\.+$/m', '/\.{2,}/'],
            ['', '.', '', '.'],
            $termination
        );

        if( gettype($termination) === 'string' ){
            
            $termination = strtolower($termination);
    
            return [
                ".$termination" => [
                    'final' => preg_quote(".$termination"),
                    'middle' => preg_quote(".$termination.")
                ]
            ];

        }

        $newArray = [];

        foreach($termination as $value){

            $value = strtolower($value);

            $newArray[".$value"] = [
                'final' => '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+(' . preg_quote(".$termination") . ')$/um',
                'middle' => preg_quote(".$termination.")
            ];

        }

        return $newArray;

    }

    /**
     * Internal handler for adding terminations (no normalization)
     * @param string|array $terminations Raw termination(s) to add
     * @access private
     */
    private static function core_add_terminations( string|array $terminations ): void {
        if( gettype($terminations) === 'string' ){
            self::$ALLOWED_TERMINATIONS = array_merge( self::$ALLOWED_TERMINATIONS, self::normalize_termination($terminations) );
        }
        else{
            self::$ALLOWED_TERMINATIONS = array_merge( self::$ALLOWED_TERMINATIONS, self::normalize_termination($terminations) );
        }
    }

    /**
     * Internal handler for removing terminations
     * @param string|array $terminations Termination(s) to remove
     * @access private
     */
    private static function core_remove_terminations( string|array $terminations ): void {

        if( gettype($terminations) === 'string' ){

            $terminations = preg_replace(
                ['/\s+/', '/[,;]/', '/\.+$/m', '/\.{2,}/'],
                ['', '.', '', '.'],
                $terminations
            );

            $terminations = strtolower($terminations);

            unset(self::$ALLOWED_TERMINATIONS[$terminations]);

        }
        else{
            
            $terminations = self::normalize_array($terminations);

            $terminations = preg_replace(
                ['/\s+/', '/[,;]/', '/\.+$/m', '/\.{2,}/'],
                ['', '.', '', '.'],
                $terminations
            );

            $terminations = array_flip($terminations);

            $terminations = array_change_key_case($terminations, CASE_LOWER);

            self::$ALLOWED_TERMINATIONS = array_diff( self::$ALLOWED_TERMINATIONS, $terminations );

        }

    }

    /**
     * Replaces all terminations and rebuilds validation patterns
     * @param array $terminations New termination list (overwrites existing)
     * @access private
     */
    private static function core_replace_terminations( array $terminations ): void {

        $terminations = self::normalize_array($terminations);

        $terminations = preg_replace(
            ['/\s+/', '/[,;]/', '/\.+$/m', '/\.{2,}/'],
            ['', '.', '', '.'],
            $terminations
        );

        self::$ALLOWED_TERMINATIONS = self::normalize_termination($terminations);

    }

    /**
     * Adds domain termination(s) for validation
     * @param string|array $terminations Termination(s) (with or without dot)
     * 
     * @example
     * $mailcheck->add_terminations(['.io', '.tech']);
     */
    public function add_terminations( string|array $terminations ): void {
        self::core_add_terminations($terminations);
    }

    /**
     * Removes termination(s) from validation
     * @param string|array $terminations Termination(s) to remove
     */
    public function remove_terminations( string|array $terminations ): void {
        self::core_remove_terminations($terminations);
    }

    /**
     * Replaces all allowed terminations (instance version)
     * @param array $terminations New terminations list
     * 
     * @example
     * $mailcheck->replace_terminations(['.io', '.ai', '.tech']);
     */
    public function replace_terminations( array $terminations ): void {
        self::core_replace_terminations($terminations);
    }

    /**
     * Adds allowed email terminations (static)
     * @param string|array $terminations Termination(s) to add
     */
    public static function add_terminationsStatic( string|array $terminations ): void {
        self::core_add_terminations($terminations);
    }

    /**
     * Static alias for remove_terminations()
     * @param string|array $terminations Termination(s) to remove
     */
    public static function remove_terminationsStatic( string|array $terminations ): void {
        self::core_remove_terminations($terminations);
    }

    /**
     * Static alias for replace_terminations()
     * @param array $terminations New terminations list
     */
    public static function replace_terminationsStatic( array $terminations ): void {
        self::core_replace_terminations($terminations);
    }

    /**
     * Compiles provider regex pattern
     */
    private static function set_providersRegex(): void {
        self::$REGEX_PROVIDERS = self::normalize_array(self::$ALLOWED_PROVIDERS, true);
        self::$REGEX_PROVIDERS = implode('|', self::$REGEX_PROVIDERS);
        self::$REGEX_PROVIDERS = '/^[a-zA-Z0-9._%+\-\p{L}]+@(' . self::$REGEX_PROVIDERS . ')$/';
    }

    /**
     * Internal handler for adding providers (no normalization)
     * @param string|array $providers Raw provider(s) to add
     * @access private
     */
    private static function core_add_providers( string|array $providers ): void {
        if( gettype($providers) === 'string' ) self::$ALLOWED_PROVIDERS[] = $providers;
        else self::$ALLOWED_PROVIDERS = array_merge( self::$ALLOWED_PROVIDERS, self::normalize_array($providers) );
        self::set_providersRegex();
    }

    /**
     * Internal handler for provider removal
     * @param string|array $providers Provider(s) to remove
     * @access private
     */
    private static function core_remove_providers( string|array $providers ): void {

        if( gettype($providers) === 'string' ){
            if( ( $key = array_search($providers, self::$ALLOWED_PROVIDERS) ) !== false ){
                unset(self::$ALLOWED_PROVIDERS[$key]);
                unset($key);
            }
        }
        else{
            
            $providers = self::normalize_array($providers);

            self::$ALLOWED_PROVIDERS = array_diff(self::$ALLOWED_PROVIDERS, $providers);

        }

        self::set_providersRegex();

    }

    /**
     * Replaces entire provider whitelist and recompiles regex
     * @param array $providers New provider list (overwrites existing)
     * @access private
     */
    private static function core_replace_providers( array $providers ): void {
        self::$ALLOWED_PROVIDERS = self::normalize_array($providers);
        self::set_providersRegex();
    }

    /**
     * Adds provider(s) to whitelist (instance version)
     * @param string|array $providers Provider(s) to add
     * 
     * @example
     * $mailcheck->add_providers(['protonmail.com', 'icloud.com']);
     */
    public function add_providers( string|array $providers ): void {
        self::core_add_providers($providers);
    }

    /**
     * Removes provider(s) from whitelist (instance version)
     * @param string|array $providers Provider(s) to remove
     */
    public function remove_providers( string|array $providers ): void {
        self::core_remove_providers($providers);
    }

    /**
     * Replaces all allowed providers (instance version)
     * @param array $providers New provider list
     * 
     * @example
     * $mailcheck->replace_providers(['company.com', 'trusted.org']);
     */
    public function replace_providers( array $providers ): void {
        self::core_replace_providers($providers);
    }

    /**
     * Adds provider(s) to whitelist (static)
     * @param string|array $providers Provider(s) to add
     * 
     * @example
     * Mailcheck::add_providersStatic('protonmail.com');
     */
    public static function add_providersStatic( string|array $providers ): void {
        self::core_add_providers($providers);
    }

    /**
     * Removes provider(s) from whitelist
     * @param string|array $providers Provider(s) to remove
     */
    public static function remove_providersStatic( string|array $providers ): void {
        self::core_remove_providers($providers);
    }

    /**
     * Replaces entire provider whitelist
     * @param array $providers New provider list
     */
    public static function replace_providersStatic( array $providers ): void {
        self::core_replace_providers($providers);
    }

    /**
     * Gets currently allowed domain terminations
     * @return array List of terminations
     */
    public static function getTerminations(): array {
        return array_keys(self::$ALLOWED_TERMINATIONS);
    }

    /**
     * Gets whitelisted email providers
     * @return array List of providers
     */
    public static function getProviders(): array {
        return self::$ALLOWED_PROVIDERS;
    }

    /**
     * Returns namespaced constants for validation modes
     * @return array [MODE_DEFAULT => 0, ...]
     */
    public static function getModes(): array {
        return self::MODES;
    }

    /**
     * Initializes validation patterns
     */
    private static function init(): void {
        self::set_providersRegex();
        self::$INITIALIZED = TRUE;
    }

    /**
     * Validates email(s) against current rules
     * @param string|array $email Email(s) to check
     * @param int|string $type Validation mode
     * @return bool True only for whitelisted patterns
     * @throws InvalidArgumentException For invalid modes
     */
    private static function core_check(string|array $email, int|string $type = self::MODE_DEFAULT): bool {
    
        if (gettype($type) === 'string') {
            $type = strtoupper(trim($type));
            if (!array_key_exists($type, self::MODES)) {
                throw new InvalidArgumentException(
                    "Invalid validation mode. Expected: " .
                    "0 (MODE_DEFAULT), 1 (MODE_TERMINATION), " .
                    "or 2 (MODE_PROVIDER), " .
                    "Received: " . var_export($type, true)
                );
            }
            $type = self::MODES[$type];
        }
    
        $regex = '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+$/mu';
    
        if ($type !== self::MODE_DEFAULT) {

            if ($type === self::MODE_TERMINATION) {

                if (is_array($email)) {

                    foreach ($email as $mail) {

                        $valid = false;

                        foreach (self::$ALLOWED_TERMINATIONS as $patterns) {

                            if (preg_match($patterns['final'], $mail) && 
                                !preg_match($patterns['middle'], $mail)) {
                                $valid = true;
                                break;
                            }

                        }

                        if (!$valid) return false;
                        
                    }

                    return true;

                } 
                else {

                    foreach (self::$ALLOWED_TERMINATIONS as $patterns) {

                        if (preg_match($patterns['final'], $email) && 
                            !preg_match($patterns['middle'], $email)) {
                            return true;
                        }

                    }

                    return false;
                }

            } else if ($type === self::MODE_PROVIDERS) {
                $regex = self::$REGEX_PROVIDERS;
            } else {
                throw new InvalidArgumentException(
                    "Invalid validation mode. Expected: " .
                    "0 (MODE_DEFAULT), 1 (MODE_TERMINATION), " .
                    "or 2 (MODE_PROVIDER), " .
                    "Received: " . var_export($type, true)
                );
            }
        }
        
        if (is_array($email)) {

            foreach ($email as $mail) {
                if (!preg_match($regex, $mail)) return false;
            }

            return true;

        } 
        else {
            return (bool) preg_match($regex, $email);
        }

    }

    /**
     * Validates email(s) against selected mode
     * @param string|array $email Email(s) to validate
     * @param int|string $type Validation mode
     * @return bool Validation result
     */
    public static function checkStatic( string|array $email, int|string $type = self::MODE_DEFAULT ): bool {
        if( ! self::$INITIALIZED ) self::init();
        return self::core_check($email, $type);
    }

    /**
     * Instance-based validation
     * @param string|array $email Email(s) to validate
     * @param int|string $type Validation mode
     * @return bool Validation result
     */
    public function check( string|array $email, int|string $type = self::MODE_DEFAULT ): bool {
        return self::core_check($email, $type);
    }

    /**
     * Filters email array based on validation rules
     * @param array $emails Emails to process
     * @param int|string $type Validation mode
     * @param bool $whitelist True=keep valid, False=keep invalid
     * @return array Filtered emails
     */
    private static function core_filter( array $email, int|string $type = self::MODE_DEFAULT, bool $white_list = true ): array {

        if (gettype($type) === 'string') {
            $type = strtoupper(trim($type));
            if (!array_key_exists($type, self::MODES)) {
                throw new InvalidArgumentException(
                    "Invalid validation mode. Expected: " .
                    "0 (MODE_DEFAULT), 1 (MODE_TERMINATION), " .
                    "or 2 (MODE_PROVIDER), " .
                    "Received: " . var_export($type, true)
                );
            }
            $type = self::MODES[$type];
        }
    
        $regex = '/^[a-zA-Z0-9._%+\-\p{L}]+@[a-zA-Z0-9\-]+(\.[a-zA-Z]{2,})+$/mu';

        $filter = [];
    
        if ($type !== self::MODE_DEFAULT) {

            if ($type === self::MODE_TERMINATION) {
                
                foreach ($email as $mail) {

                    $valid = false;

                    foreach (self::$ALLOWED_TERMINATIONS as $patterns) {

                        if (preg_match($patterns['final'], $mail) && 
                            !preg_match($patterns['middle'], $mail)) {

                            $valid = true;
                            break;

                        }

                    }

                    if( $valid && $white_list ) $filter[] = $mail;
                    else if( !$valid && !$white_list ) $filter[] = $mail; 

                }

                return $filter;

            } else if ($type === self::MODE_PROVIDERS) {
                $regex = self::$REGEX_PROVIDERS;
            } else {
                throw new InvalidArgumentException(
                    "Invalid validation mode. Expected: " .
                    "0 (MODE_DEFAULT), 1 (MODE_TERMINATION), " .
                    "or 2 (MODE_PROVIDER), " .
                    "Received: " . var_export($type, true)
                );
            }
        }
        
        foreach ($email as $mail) {

            if ( preg_match($regex, $mail) && $white_list ) $filter[] = $mail;
            else if ( !preg_match($regex, $mail) && !$white_list ) $filter[] = $mail;

        }

        return $filter;

    }

    /**
     * Filters email array by validation rules
     * @param array $emails Emails to filter
     * @param int|string $type Validation mode
     * @param bool $whitelist Keep valid (true) or invalid (false)
     * @return array Filtered results
     */
    public static function filterStatic( array $email, int|string $type = self::MODE_DEFAULT, bool $white_list = true ): array {
        if( ! self::$INITIALIZED ) self::init();
        return self::core_filter($email, $type, $white_list);
    }

    /**
     * Instance-based email filtering
     * @param array $emails Emails to filter
     * @param int|string $type Validation mode
     * @param bool $whitelist Keep valid (true) or invalid (false)
     * @return array Filtered results
     */
    public function filter( array $email, int|string $type = self::MODE_DEFAULT, bool $white_list = true ): array {
        return self::core_filter($email, $type, $white_list);
    }

}
