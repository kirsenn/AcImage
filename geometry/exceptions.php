<?php

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class FileNotFoundException extends Exception
{
	const MESSAGE = 'File not found';

	public function __construct()
	{
		$this->message = self::MESSAGE;
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class InvalidFileException extends Exception
{

	public function __construct($path)
	{
		$this->message = "Invalid file: $path";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class InvalidChannelException extends Exception
{

	public function __construct($chenalName)
	{
		$this->message = "Invalid channel: {$chenalName}";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class UnsupportedFormatException extends Exception
{

	public function __construct($format)
	{
		$this->message = "This image format ($format) is not supported by your version of GD library";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class GDnotInstalledException extends Exception
{
	public function __construct()
	{
		$this->message = "The GD library is not installed";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class FileAlreadyExistsException extends Exception
{
	public function __construct($path)
	{
		$this->message = "File $path is already exists!";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class FileNotSaveException extends Exception
{
	public function __construct($path)
	{
		$this->message = "File: $path not saved";
	}
}

/**
 * @package exceptions
 *
 * @author Антон Кургузенков <kurguzenkov@list.ru>
 *
 * @version 0.01
 * @since 2012-11-02
 */

class IllegalArgumentException extends Exception
{
	public function __consruct()
	{
		$this->message = "Illegal argument";
	}
}
?>