<?php

/**
 * Vapm and a brief idea of what it does.>
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm;

final class Error
{

    public const FAILED_IN_FETCHING_DATA = "Error in fetching data";

    public const WRONG_TYPE_WHEN_USE_CURL_EXEC = "curl_exec() should return string|false when CURL-OPT_RETURN-TRANSFER is set";

    public const UNABLE_START_THREAD = "Unable to start thread";

    public const DEFERRED_CALLBACK_MUST_RETURN_GENERATOR = "Deferred callback must return a Generator";

    public const UNABLE_TO_OPEN_FILE = "Error: Unable to open file!";

    public const FILE_DOES_NOT_EXIST = "Error: File does not exist!";

    public const FILE_ALREADY_EXISTS = "Error: File already exists!";

}