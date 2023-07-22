<?php

/*
 * Copyright (c) 2023 VennV, CC0 1.0 Universal
 *
 * CREATIVE COMMONS CORPORATION IS NOT A LAW FIRM AND DOES NOT PROVIDE
 * LEGAL SERVICES. DISTRIBUTION OF THIS DOCUMENT DOES NOT CREATE AN
 * ATTORNEY-CLIENT RELATIONSHIP. CREATIVE COMMONS PROVIDES THIS
 * INFORMATION ON AN "AS-IS" BASIS. CREATIVE COMMONS MAKES NO WARRANTIES
 * REGARDING THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS
 * PROVIDED HEREUNDER, AND DISCLAIMS LIABILITY FOR DAMAGES RESULTING FROM
 * THE USE OF THIS DOCUMENT OR THE INFORMATION OR WORKS PROVIDED
 * HEREUNDER.
 */

declare(strict_types = 1);

namespace vennv\vapm;

interface InternetRequestResultInterface
{

    /**
     * @return string[][]
     */
    public function getHeaders() : array;

    public function getBody() : string;

    public function getCode() : int;

}

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
