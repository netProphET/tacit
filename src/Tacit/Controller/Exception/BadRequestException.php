<?php
/*
 * This file is part of the Tacit package.
 *
 * Copyright (c) Jason Coward <jason@opengeek.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tacit\Controller\Exception;


class BadRequestException extends RestfulException
{
    protected $status = 400;
    protected $message = "Bad Request";
    protected $description = "The request could not be understood by the server due to malformed syntax. DO NOT repeat the request without modifications.";
}
