<?php

declare(strict_types=1);

namespace Yivic_Base\App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\ViewErrorBag;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [];

	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
	];

	/**
	 * Render the given HttpException.
	 *
	 * @param  \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderHttpException( HttpExceptionInterface $e ) {
		$this->registerErrorViewPaths();

		$view = $this->getHttpExceptionView( $e );

		// We want to render the view for errors when on debug mode
		//  and the environment should not be 'production'
		if (
			view()->exists( $view )
			&& ( ! config( 'app.debug' ) || config( 'app.env' ) === 'production' )
		) {
			return response()->view(
				$view,
				[
					'errors' => new ViewErrorBag(),
					'exception' => $e,
				],
				$e->getStatusCode(),
				$e->getHeaders()
			);
		}

		return $this->convertExceptionToResponse( $e );
	}

	/**
	 * @inheritedDoc
	 * @param HttpExceptionInterface $e
	 * @return string
	 */
	protected function getHttpExceptionView( HttpExceptionInterface $e ) {
		return 'yivic-base::errors/error';
	}
}