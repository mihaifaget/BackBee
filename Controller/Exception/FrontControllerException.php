<?php

/*
 * Copyright (c) 2011-2015 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */

namespace BackBee\Controller\Exception;

use Symfony\Component\HttpFoundation\Request;
use BackBee\Exception\BBException;

/**
 * Exception thrown when an HTTP request can not be handled
 * The associated HTTP Code error is obtain by decreasing the error code by 6000.
 *
 * @category    BackBee
 *
 * @copyright   Lp digital system
 * @author      c.rouillon <charles.rouillon@lp-digital.fr>
 */
class FrontControllerException extends BBException
{
    const UNKNOWN_ERROR = 6000;
    const BAD_REQUEST = 6400;
    const NOT_FOUND = 6404;
    const INTERNAL_ERROR = 6500;

    protected $_code = self::UNKNOWN_ERROR;

    /**
     * The current request handled.
     *
     * @var Request
     */
    private $_request;

    /**
     * Set the current request.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
    }

    /**
     * Return the current request.
     *
     * @return Request The current request generating an error
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_code - 6000;
    }
}
