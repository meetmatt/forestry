<?php

/**
 * Error handler which converts ALL errors to exceptions
 *
 * @param int $level
 * @param string $message
 * @param string $file
 * @param int $line
 * @return void
 */
set_error_handler(function($level, $message, $file, $line){
    debug_print_backtrace();
    echo '<pre>' . ob_get_clean();
    throw new \ErrorException(
        sprintf("[%d] %s in %s on line %d", $level, $message, $file, $line)
    );
});
