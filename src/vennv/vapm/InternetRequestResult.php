<?php

/*
 * Copyright (c) 2023 VennV
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types = 1);

namespace vennv\vapm;

final class InternetRequestResult implements InternetRequestResultInterface
{

	/**
	 * @var string[][] $headers
	 */
    private array $headers;

    private string $body;

    private int $code;

    /**
     * @param string[][] $headers
     * @param string $body
     * @param int $code
     */
	public function __construct(array $headers, string $body, int $code)
	{
        $this->headers = $headers;
        $this->body = $body;
        $this->code = $code;
    }

	/**
	 * @return string[][]
	 */
	public function getHeaders() : array
	{ 
		return $this->headers; 
	}

	public function getBody() : string
	{ 
		return $this->body; 
	}

	public function getCode() : int
	{ 
		return $this->code; 
	}

}
