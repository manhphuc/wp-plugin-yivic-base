<?php

declare(strict_types=1);

namespace Yivic_Base\App\Support\Traits;

trait Yivic_Base_Trans_Trait {
	/**
	 * Translate a text using the plugin's text domain
	 *
	 * @param string $untranslated_text Text to be translated
	 *
	 * @return string Translated text
	 * @throws \Exception
	 */
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function __( $untranslated_text ): string {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
		return __( $untranslated_text, 'yivic' );
	}

	/**
	 * Translate a text using the plugin's text domain
	 *
	 * @param string $untranslated_text Text to be translated
	 * @param string $context for the translation
	 *
	 * @return string Translated text
	 * @throws \Exception
	 */
	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function _x( $untranslated_text, $context ): string {
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralContext, WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
		return _x( $untranslated_text, $context, 'yivic' );
	}
}
