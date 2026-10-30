<?php

namespace App;

use RuntimeException;

/**
 * Something went wrong talking to Facebook. The message is written for the officer and can be shown as is.
 */
class FacebookImportException extends RuntimeException {}
