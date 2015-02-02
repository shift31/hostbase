<?php namespace Hostbase\ErrorHandling;

use Exception;
use Hostbase\Entity\Exceptions\EntityAlreadyExists;
use Hostbase\Entity\Exceptions\EntityNotFound;
use Hostbase\Entity\Exceptions\InvalidEntity;
use Illuminate\Support\ServiceProvider;
use Request;


/**
 * Class ErrorHandlerServiceProvider
 *
 * @package Hostbase\ErrorHandling
 */
class ErrorHandlerServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->error(function(Exception $exception)
        {
            ErrorHandler::logException($exception);
            return ErrorHandler::errorInternalError($exception->getMessage(), (new \ReflectionClass($exception))->getShortName());
        });


        // always return json error response for 404s
        $this->app->missing(function()
        {
            $requestUri = Request::getRequestUri();

            return ErrorHandler::errorNotFound("The request URI '$requestUri' was not found");
        });


        $this->app->error(function(EntityNotFound $exception)
        {
            // todo - evaluate the need for logging 'not found' exceptions
            ErrorHandler::logException($exception);
            return ErrorHandler::errorNotFound($exception->getMessage());
        });


        $this->app->error(function(EntityAlreadyExists $exception)
        {
            ErrorHandler::logException($exception);
            return ErrorHandler::errorConflict($exception->getMessage());
        });


        $this->app->error(function(InvalidEntity $exception) {
            ErrorHandler::logException($exception);
            return ErrorHandler::errorWrongArgs($exception->getMessage());
        });
    }
}