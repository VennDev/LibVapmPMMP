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

namespace vennv;

use Closure;
use CurlHandle;
use function array_merge;
use function curl_close;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function explode;
use function is_string;
use function strtolower;
use function substr;
use function trim;
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_AUTOREFERER;
use const CURLOPT_CONNECTTIMEOUT_MS;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_FORBID_REUSE;
use const CURLOPT_FRESH_CONNECT;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_TIMEOUT_MS;

final class Internet
{

	/**
	 * GETs a URL using cURL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param int         $timeout      default 10
	 * @param string[]    $extraHeaders
	 * @param string|null $error        reference parameter, will be set to the output of curl_error(). Use this to retrieve errors that occurred during the operation.
	 */
	public static function getURL(
        string $page,
        int $timeout = 10,
        array $extraHeaders = [],
        string &$error = null
	) : ?InternetRequestResult
	{
		try
		{
			return self::simpleCurl(
				$page, 
				$timeout, 
				$extraHeaders
			);
		}
		catch (InternetException $exception)
		{
			$error = $exception->getMessage();
			return null;
		}
	}

	/**
	 * POSTs data to a URL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param string[]|string $args
	 * @param string[]        $extraHeaders
	 * @param string|null     $error          reference parameter, will be set to the output of curl_error(). Use this to retrieve errors that occurred during the operation.
	 */
	public static function postURL(
		string $page, 
		array|string $args, 
		int $timeout = 10, 
		array $extraHeaders = [], 
		string &$error = null
	) : ?InternetRequestResult
	{
		try
		{
			return self::simpleCurl($page, $timeout, $extraHeaders, [
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $args
			]);
		}
		catch (InternetException $ex)
		{
			$error = $ex->getMessage();
			return null;
		}
	}

	/**
	 * General cURL shorthand function.
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
	 *
	 * @param float         $timeout      The maximum connect timeout and timeout in seconds, correct to ms.
	 * @param string[]      $extraHeaders extra headers to send as a plain string array
	 * @param array         $extraOpts    extra CURL-OPT_* to set as an [opt => value] map
	 * @param Closure|null $onSuccess     function to be called if there is no error. Accepts a resource argument as the cURL handle.
     * @phpstan-param array<int, mixed>                $extraOpts
     * @phpstan-param list<string>                     $extraHeaders
     * @phpstan-param (Closure(CurlHandle) : void)|null $onSuccess
     *
     * @throws InternetException if a cURL error occurs
	 */
	public static function simpleCurl(
		string $page, 
		float $timeout = 10, 
		array $extraHeaders = [], 
		array $extraOpts = [], 
		?Closure $onSuccess = null
	) : InternetRequestResult
	{

		$time = (int) ($timeout * 1000);

		$curlHandle = curl_init($page);

        if ($curlHandle === false)
		{
			throw new InternetException(
				"Unable to create new cURL session"
			);
		}

		curl_setopt_array($curlHandle, $extraOpts + 
		[
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT_MS => $time,
			CURLOPT_TIMEOUT_MS => $time,
			CURLOPT_HTTPHEADER => array_merge(
				["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0"],
				$extraHeaders
			),
			CURLOPT_HEADER => true
		]);

		try
		{
			$raw = curl_exec($curlHandle);

			if ($raw === false)
			{
				throw new InternetException(curl_error($curlHandle));
			}

			if (!is_string($raw))
			{
				throw new AssumptionFailedError(Error::WRONG_TYPE_WHEN_USE_CURL_EXEC);
			}

			$httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
			$headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
			$rawHeaders = substr($raw, 0, $headerSize);
			$body = substr($raw, $headerSize);
			$headers = [];

            foreach (explode("\r\n\r\n", $rawHeaders) as $rawHeaderGroup)
			{
				$headerGroup = [];

				foreach (explode("\r\n", $rawHeaderGroup) as $line)
				{
					$nameValue = explode(":", $line, 2);

					if (isset($nameValue[1]))
					{
						$headerGroup[trim(strtolower($nameValue[0]))] = trim($nameValue[1]);
					}

				}

				$headers[] = $headerGroup;
			}

			if (!is_null($onSuccess))
			{
				$onSuccess($curlHandle);
			}

            return new InternetRequestResult($headers, $body, $httpCode);
		}
		finally
		{
			curl_close($curlHandle);
		}
	}

}
