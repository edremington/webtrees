<?php namespace Fisharebest\Localization\Language;

use Fisharebest\Localization\PluralRule\PluralRuleOneTwoOther;
use Fisharebest\Localization\Territory\TerritoryFi;

/**
 * Class LanguageSmi - Representation of the Sami languages.
 *
 * @author    Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2015 Greg Roach
 * @license   GPLv3+
 */
class LanguageSmi extends AbstractLanguage implements LanguageInterface {
	/** {@inheritdoc} */
	public function code() {
		return 'smi';
	}

	/** {@inheritdoc} */
	public function defaultTerritory() {
		return new TerritoryFi;
	}

	/** {@inheritdoc} */
	public function pluralRule() {
		return new PluralRuleOneTwoOther;
	}
}