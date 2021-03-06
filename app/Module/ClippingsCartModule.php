<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2018 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Fisharebest\Webtrees\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\ClippingsCart\ClippingsCartController;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;

/**
 * Class ClippingsCartModule
 */
class ClippingsCartModule extends AbstractModule implements ModuleMenuInterface, ModuleSidebarInterface {
	/** {@inheritdoc} */
	public function getTitle() {
		return /* I18N: Name of a module */
			I18N::translate('Clippings cart');
	}

	/** {@inheritdoc} */
	public function getDescription() {
		return /* I18N: Description of the “Clippings cart” module */
			I18N::translate('Select records from your family tree and save them as a GEDCOM file.');
	}

	/**
	 * What is the default access level for this module?
	 *
	 * Some modules are aimed at admins or managers, and are not generally shown to users.
	 *
	 * @return int
	 */
	public function defaultAccessLevel() {
		return Auth::PRIV_USER;
	}

	/**
	 * This is a general purpose hook, allowing modules to respond to routes
	 * of the form module.php?mod=FOO&mod_action=BAR
	 *
	 * @param string $mod_action
	 */
	public function modAction($mod_action) {
		global $WT_TREE;

		// Only allow access if either the menu or sidebar is enabled.
		if (
			!array_key_exists($this->getName(), Module::getActiveSidebars($WT_TREE)) &&
			!array_key_exists($this->getName(), Module::getActiveMenus($WT_TREE))
		) {
			http_response_code(404);

			return;
		}

		switch ($mod_action) {
			case 'ajax':
				$html = $this->getSidebarAjaxContent();
				header('Content-Type: text/html; charset=UTF-8');
				echo $html;
				break;
			case 'index':
				global $controller, $WT_TREE;

				$MAX_PEDIGREE_GENERATIONS = $WT_TREE->getPreference('MAX_PEDIGREE_GENERATIONS');

				$clip_ctrl = new ClippingsCartController;
				$cart      = Session::get('cart');

				$controller = new PageController;
				$controller
					->setPageTitle($this->getTitle())
					->pageHeader();

				echo '<script>';
				echo 'function radAncestors(elementid) {var radFamilies=document.getElementById(elementid);radFamilies.checked=true;}';
				echo '</script>';
				echo '<div class="clipping-cart">';

				if (!$cart[$WT_TREE->getTreeId()]) {
					echo '<h2>', I18N::translate('Family tree clippings cart'), '</h2>';
				}

				if ($clip_ctrl->action == 'add') {
					$record = GedcomRecord::getInstance($clip_ctrl->id, $WT_TREE);
					if ($clip_ctrl->type === 'FAM') { ?>
					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<input type="hidden" name="id" value="<?= $clip_ctrl->id ?>">
						<input type="hidden" name="type" value="<?= $clip_ctrl->type ?>">
						<input type="hidden" name="action" value="add1">
						<table class="add-to center">
							<thead>
								<tr>
									<td class="topbottombar">
										<?= I18N::translate('Add to the clippings cart') ?>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="optionbox">
										<input type="radio" name="others" value="parents">
										<?= $record->getFullName() ?>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<input type="radio" name="others" value="members" checked>
										<?= /* I18N: %s is a family (husband + wife) */
											I18N::translate('%s and their children', $record->getFullName()) ?>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<input type="radio" name="others" value="descendants">
										<?= /* I18N: %s is a family (husband + wife) */
											I18N::translate('%s and their descendants', $record->getFullName()) ?>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td class="topbottombar"><input type="submit" value="<?= I18N::translate('continue') ?>">
									</td>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<?php } elseif ($clip_ctrl->type === 'INDI') { ?>
					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<input type="hidden" name="id" value="<?= $clip_ctrl->id ?>">
						<input type="hidden" name="type" value="<?= $clip_ctrl->type ?>">
						<input type="hidden" name="action" value="add1">
						<table class="add-to center">
							<thead>
								<tr>
									<td class="topbottombar">
										<?= I18N::translate('Add to the clippings cart') ?>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" checked value="none">
											<?= $record->getFullName() ?>
										</label>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="parents">
											<?php
												if ($record->getSex() === 'F') {
													echo /* I18N: %s is a woman's name */
													I18N::translate('%s, her parents and siblings', $record->getFullName());
												} else {
													echo /* I18N: %s is a man's name */
													I18N::translate('%s, his parents and siblings', $record->getFullName());
												}
												?>
										</label>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="members">
											<?php
												if ($record->getSex() === 'F') {
													echo /* I18N: %s is a woman's name */
													I18N::translate('%s, her spouses and children', $record->getFullName());
												} else {
													echo /* I18N: %s is a man's name */
													I18N::translate('%s, his spouses and children', $record->getFullName());
												}
												?>
										</label>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="ancestors" id="ancestors">
											<?php
												if ($record->getSex() === 'F') {
													echo /* I18N: %s is a woman's name */
													I18N::translate('%s and her ancestors', $record->getFullName());
												} else {
													echo /* I18N: %s is a man's name */
													I18N::translate('%s and his ancestors', $record->getFullName());
												}
												?>
										</label>
										<br>
										<?= I18N::translate('Number of generations') ?>
											<input type="text" size="5" name="level1" value="<?= $MAX_PEDIGREE_GENERATIONS ?>" onfocus="radAncestors('ancestors');">
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="ancestorsfamilies" id="ancestorsfamilies">
											<?php
												if ($record->getSex() === 'F') {
													echo /* I18N: %s is a woman's name */
													I18N::translate('%s, her ancestors and their families', $record->getFullName());
												} else {
													echo /* I18N: %s is a man's name */
													I18N::translate('%s, his ancestors and their families', $record->getFullName());
												}
												?>
										</label>
										<br>
										<?= I18N::translate('Number of generations') ?>
											<input type="text" size="5" name="level2" value="<?= $MAX_PEDIGREE_GENERATIONS ?>" onfocus="radAncestors('ancestorsfamilies');">
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="descendants" id="descendants">
											<?php
												if ($record->getSex() === 'F') {
													echo /* I18N: %s is a woman's name */
													I18N::translate('%s, her spouses and descendants', $record->getFullName());
												} else {
													echo /* I18N: %s is a man's name */
													I18N::translate('%s, his spouses and descendants', $record->getFullName());
												}
												?>
										</label>
										<br>
										<?= I18N::translate('Number of generations') ?>
											<input type="text" size="5" name="level3" value="<?= $MAX_PEDIGREE_GENERATIONS ?>" onfocus="radAncestors('descendants');">
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td class="topbottombar">
										<input type="submit" value="<?= I18N::translate('continue') ?>">
									</td>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<?php } elseif ($clip_ctrl->type === 'SOUR') { ?>
					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<input type="hidden" name="id" value="<?= $clip_ctrl->id ?>">
						<input type="hidden" name="type" value="<?= $clip_ctrl->type ?>">
						<input type="hidden" name="action" value="add1">
						<table class="add-to center">
							<thead>
								<tr>
									<td class="topbottombar">
										<?= I18N::translate('Add to the clippings cart') ?>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" checked value="none">
											<?= $record->getFullName() ?>
										</label>
									</td>
								</tr>
								<tr>
									<td class="optionbox">
										<label>
											<input type="radio" name="others" value="linked">
											<?= /* I18N: %s is the name of a source */
												I18N::translate('%s and the individuals that reference it.', $record->getFullName()) ?>
										</label>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td class="topbottombar">
										<input type="submit" value="<?= I18N::translate('continue') ?>">
									</td>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<?php }
				}

				if (!$cart[$WT_TREE->getTreeId()]) {
					if ($clip_ctrl->action != 'add') {
						echo '<div class="center">';
						echo I18N::translate('The clippings cart allows you to take extracts from this family tree and download them as a GEDCOM file.');
						echo '</div>';
						?>
					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" name="addin" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<table class="add-to center">
							<thead>
								<tr>
									<td colspan="2" class="topbottombar">
										<?= I18N::translate('Add to the clippings cart') ?>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="optionbox">
										<input type="hidden" name="action" value="add">
										<input type="text" data-autocomplete-type="IFSRO" name="id" id="cart_item_id" size="5">
									</td>
									<td class="optionbox">
										<input type="submit" value="<?= /* I18N: A button label. */ I18N::translate('add') ?>">
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</div>
					<?php
					}
					echo '<div class="center">';
					// -- end new lines
					echo I18N::translate('Your clippings cart is empty.');
					echo '</div>';
				} else {
					// Keep track of the INDI from the parent page, otherwise it will
					// get lost after ajax updates
					$pid = Filter::get('pid', WT_REGEX_XREF);

					if ($clip_ctrl->action !== 'download' && $clip_ctrl->action !== 'add') { ?>
					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<input type="hidden" name="action" value="download">
						<input type="hidden" name="pid" value="<?= $pid ?>">
						<table class="add-to center">
							<tr>
								<td colspan="2" class="topbottombar">
									<h2><?= I18N::translate('Download') ?></h2>
								</td>
							</tr>
							<?php if (Auth::isManager($WT_TREE)) { ?>
								<tr>
									<td class="descriptionbox width50 wrap">
										<?= I18N::translate('Apply privacy settings') ?>
									</td>
									<td class="optionbox">
										<input type="radio" name="privatize_export" value="none" checked>
										<?= I18N::translate('None') ?>
										<br>
										<input type="radio" name="privatize_export" value="gedadmin">
										<?= I18N::translate('Manager') ?>
										<br>
										<input type="radio" name="privatize_export" value="user">
										<?= I18N::translate('Member') ?>
										<br>
										<input type="radio" name="privatize_export" value="visitor">
										<?= I18N::translate('Visitor') ?>
									</td>
								</tr>
							<?php } elseif (Auth::isMember($WT_TREE)) { ?>
								<tr>
									<td class="descriptionbox width50 wrap">
										<?= I18N::translate('Apply privacy settings') ?>
									</td>
									<td class="optionbox">
										<input type="radio" name="privatize_export" value="user" checked> <?= I18N::translate('Member') ?><br>
										<input type="radio" name="privatize_export" value="visitor"> <?= I18N::translate('Visitor') ?>
									</td>
								</tr>
							<?php } ?>

							<tr>
								<td class="descriptionbox width50 wrap">
									<?= I18N::translate('Convert from UTF-8 to ISO-8859-1') ?>
								</td>
								<td class="optionbox">
									<input type="checkbox" name="convert" value="yes">
								</td>
							</tr>

							<tr>
								<td class="topbottombar" colspan="2">
									<input type="submit" value="<?= /* I18N: A button label. */ I18N::translate('download') ?>">
								</td>
							</tr>
						</table>
					</form>
				</div>
					<br>

					<form class="wt-page-options wt-page-options-clipping-cart hidden-print" name="addin" action="module.php">
						<input type="hidden" name="mod" value="clippings">
						<input type="hidden" name="mod_action" value="index">
						<table class="add-to center">
							<thead>
								<tr>
									<td colspan="2" class="topbottombar" style="text-align:center; ">
										<?= I18N::translate('Add to the clippings cart') ?>
									</td>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="optionbox">
										<input type="hidden" name="action" value="add">
										<input type="text" data-autocomplete-type="IFSRO" name="id" id="cart_item_id" size="8">
									</td>
									<td class="optionbox">
										<input type="submit" value="<?= /* I18N: A button label. */ I18N::translate('add') ?>">
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="2">
										<a href="module.php?mod=clippings&amp;mod_action=index&amp;action=empty">
											<?= I18N::translate('Empty the clippings cart') ?>
										</a>
									</th>
								</tr>
							</tfoot>
						</table>
					</form>
				</div>
				<?php } ?>
				<div class="clipping-cart">
				<h2>
					<?= I18N::translate('Family tree clippings cart') ?>
				</h2>
				<table id="mycart" class="sortable list_table width50">
					<thead>
						<tr>
							<th class="list_label"><?= I18N::translate('Record') ?></th>
							<th class="list_label"><?= I18N::translate('Remove') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach (array_keys($cart[$WT_TREE->getTreeId()]) as $xref) {
								$record = GedcomRecord::getInstance($xref, $WT_TREE);
								if ($record) {
									switch ($record::RECORD_TYPE) {
										case 'INDI':
											$icon = 'icon-indis';
											break;
										case 'FAM':
											$icon = 'icon-sfamily';
											break;
										case 'SOUR':
											$icon = 'icon-source';
											break;
										case 'REPO':
											$icon = 'icon-repository';
											break;
										case 'NOTE':
											$icon = 'icon-note';
											break;
										case 'OBJE':
											$icon = 'icon-media';
											break;
										default:
											$icon = 'icon-clippings';
											break;
									}
								?>
								<tr>
									<td class="list_value">
										<i class="<?= $icon ?>"></i>
										<?php
										echo '<a href="', e($record->url()), '">', $record->getFullName(), '</a>';
										?>
									</td>
									<td class="list_value center vmiddle"><a href="module.php?mod=clippings&amp;mod_action=index&amp;action=remove&amp;id=<?= $xref ?>" class="icon-remove" title="<?= I18N::translate('Remove') ?>"></a></td>
								</tr>
								<?php
							}
						}
						?>
				</table>
			</div>
				<?php
			}
			break;
			default:
				http_response_code(404);
				break;
		}
	}

	/**
	 * The user can re-order menus. Until they do, they are shown in this order.
	 *
	 * @return int
	 */
	public function defaultMenuOrder() {
		return 20;
	}

	/**
	 * A menu, to be added to the main application menu.
	 *
	 * @param Tree $tree
	 *
	 * @return Menu|null
	 */
	public function getMenu(Tree $tree) {
		global $controller;

		$submenus = [];
		if (isset($controller->record)) {
			$submenus[] = new Menu($this->getTitle(), 'module.php?mod=clippings&amp;mod_action=index&amp;ged=' . $tree->getNameUrl(), 'menu-clippings-cart', ['rel' => 'nofollow']);
		}
		if (!empty($controller->record) && $controller->record->canShow()) {
			$submenus[] = new Menu(I18N::translate('Add to the clippings cart'), 'module.php?mod=clippings&amp;mod_action=index&amp;action=add&amp;id=' . $controller->record->getXref(), 'menu-clippings-add', ['rel' => 'nofollow']);
		}

		if ($submenus) {
			return new Menu($this->getTitle(), '#', 'menu-clippings', ['rel' => 'nofollow'], $submenus);
		} else {
			return new Menu($this->getTitle(), 'module.php?mod=clippings&amp;mod_action=index&amp;ged=' . $tree->getNameUrl(), 'menu-clippings', ['rel' => 'nofollow']);
		}
	}

	/** {@inheritdoc} */
	public function defaultSidebarOrder() {
		return 60;
	}

	/** {@inheritdoc} */
	public function hasSidebarContent(Individual $individual) {
		// Creating a controller has the side effect of initialising the cart
		new ClippingsCartController;

		return true;
	}

	/**
	 * Load this sidebar synchronously.
	 *
	 * @param Individual $individual
	 *
	 * @return string
	 */
	public function getSidebarContent(Individual $individual) {
		global $controller;

		$controller->addInlineJavascript('
				$("#sb_clippings_content").on("click", ".add_cart, .remove_cart", function() {
					$("#sb_clippings_content").load(this.href);
					return false;
				});
			');

		return '<div id="sb_clippings_content">' . $this->getCartList() . '</div>';
	}

	/** {@inheritdoc} */
	public function getSidebarAjaxContent() {
		global $WT_TREE;

		$cart = Session::get('cart');

		$clip_ctrl         = new ClippingsCartController;
		$add               = Filter::get('add', WT_REGEX_XREF);
		$add1              = Filter::get('add1', WT_REGEX_XREF);
		$remove            = Filter::get('remove', WT_REGEX_XREF);
		$others            = Filter::get('others');
		$clip_ctrl->level1 = Filter::getInteger('level1');
		$clip_ctrl->level2 = Filter::getInteger('level2');
		$clip_ctrl->level3 = Filter::getInteger('level3');
		if ($add) {
			$record = GedcomRecord::getInstance($add, $WT_TREE);
			if ($record) {
				$clip_ctrl->id   = $record->getXref();
				$clip_ctrl->type = $record::RECORD_TYPE;
				$clip_ctrl->addClipping($record);
			}
		} elseif ($add1) {
			$record = Individual::getInstance($add1, $WT_TREE);
			if ($record) {
				$clip_ctrl->id   = $record->getXref();
				$clip_ctrl->type = $record::RECORD_TYPE;
				if ($others == 'parents') {
					foreach ($record->getChildFamilies() as $family) {
						$clip_ctrl->addClipping($family);
						$clip_ctrl->addFamilyMembers($family);
					}
				} elseif ($others == 'ancestors') {
					$clip_ctrl->addAncestorsToCart($record, $clip_ctrl->level1);
				} elseif ($others == 'ancestorsfamilies') {
					$clip_ctrl->addAncestorsToCartFamilies($record, $clip_ctrl->level2);
				} elseif ($others == 'members') {
					foreach ($record->getSpouseFamilies() as $family) {
						$clip_ctrl->addClipping($family);
						$clip_ctrl->addFamilyMembers($family);
					}
				} elseif ($others == 'descendants') {
					foreach ($record->getSpouseFamilies() as $family) {
						$clip_ctrl->addClipping($family);
						$clip_ctrl->addFamilyDescendancy($family, $clip_ctrl->level3);
					}
				}
			}
		} elseif ($remove) {
			unset($cart[$WT_TREE->getTreeId()][$remove]);
			Session::put('cart', $cart);
		} elseif (isset($_REQUEST['empty'])) {
			$cart[$WT_TREE->getTreeId()] = [];
			Session::put('cart', $cart);
		} elseif (isset($_REQUEST['download'])) {
			return $this->downloadForm();
		}

		return $this->getCartList();
	}

	/**
	 * A list for the side bar.
	 *
	 * @return string
	 */
	public function getCartList() {
		global $WT_TREE;

		$cart = Session::get('cart', []);
		if (!array_key_exists($WT_TREE->getTreeId(), $cart)) {
			$cart[$WT_TREE->getTreeId()] = [];
		}
		$pid = Filter::get('pid', WT_REGEX_XREF);

		if (!$cart[$WT_TREE->getTreeId()]) {
			$out = I18N::translate('Your clippings cart is empty.');
		} else {
			$out = '';
			if (!empty($cart[$WT_TREE->getTreeId()])) {
				$out .=
					'<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=ajax&amp;empty=true&amp;pid=' . $pid . '" class="remove_cart">' .
					I18N::translate('Empty the clippings cart') .
					'</a>' .
					'<br>' .
					'<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=ajax&amp;download=true&amp;pid=' . $pid . '" class="add_cart">' .
					I18N::translate('Download') .
					'</a><br><br>';
			}
			$out .= '<ul>';
			foreach (array_keys($cart[$WT_TREE->getTreeId()]) as $xref) {
				$record = GedcomRecord::getInstance($xref, $WT_TREE);
				if ($record instanceof Individual || $record instanceof Family) {
					switch ($record::RECORD_TYPE) {
						case 'INDI':
							$icon = 'icon-indis';
							break;
						case 'FAM':
							$icon = 'icon-sfamily';
							break;
					}
					$out .= '<li>';
					if (!empty($icon)) {
						$out .= '<i class="' . $icon . '"></i>';
					}
					$out .= '<a href="' . e($record->url()) . '">';
					if ($record instanceof Individual) {
						$out .= $record->getSexImage();
					}
					$out .= ' ' . $record->getFullName() . ' ';
					if ($record instanceof Individual && $record->canShow()) {
						$out .= ' (' . $record->getLifeSpan() . ')';
					}
					$out .= '</a>';
					$out .= '<a class="icon-remove remove_cart" href="module.php?mod=' . $this->getName() . '&amp;mod_action=ajax&amp;remove=' . $xref . '&amp;pid=' . $pid . '" title="' . I18N::translate('Remove') . '"></a>';
					$out .= '</li>';
				}
			}
			$out .= '</ul>';
		}

		$record = Individual::getInstance($pid, $WT_TREE);
		if ($record && !array_key_exists($record->getXref(), $cart[$WT_TREE->getTreeId()])) {
			$out .= '<br><a href="module.php?mod=' . $this->getName() . '&amp;mod_action=ajax&amp;action=add1&amp;type=INDI&amp;id=' . $pid . '&amp;pid=' . $pid . '" class="add_cart"><i class="icon-clippings"></i> ' . I18N::translate('Add %s to the clippings cart', $record->getFullName()) . '</a>';
		}

		return $out;
	}

	/**
	 * A form to choose the download options.
	 *
	 * @return string
	 */
	public function downloadForm() {
		global $WT_TREE;

		$pid = Filter::get('pid', WT_REGEX_XREF);

		$out = '<script>';
		$out .= 'function cancelDownload() {
				var link = "module.php?mod=' . $this->getName() . '&mod_action=ajax&pid=' . $pid . '";
				$("#sb_clippings_content").load(link);
			}';
		$out .= '</script>';
		$out .= '<form class="wt-page-options wt-page-options-clipping-cart hidden-print" action="module.php">
		<input type="hidden" name="mod" value="clippings">
		<input type="hidden" name="mod_action" value="index">
		<input type="hidden" name="pid" value="' . $pid . '">
		<input type="hidden" name="action" value="download">
		<table>
		<tr><td colspan="2" class="topbottombar"><h2>' . I18N::translate('Download') . '</h2></td></tr>
		';

		if (Auth::isManager($WT_TREE)) {
			$out .=
				'<tr><td class="descriptionbox width50 wrap">' . I18N::translate('Apply privacy settings') . '</td>' .
				'<td class="optionbox">' .
				'<input type="radio" name="privatize_export" value="none" checked> ' . I18N::translate('None') . '<br>' .
				'<input type="radio" name="privatize_export" value="gedadmin"> ' . I18N::translate('Manager') . '<br>' .
				'<input type="radio" name="privatize_export" value="user"> ' . I18N::translate('Member') . '<br>' .
				'<input type="radio" name="privatize_export" value="visitor"> ' . I18N::translate('Visitor') .
				'</td></tr>';
		} elseif (Auth::isMember($WT_TREE)) {
			$out .=
				'<tr><td class="descriptionbox width50 wrap">' . I18N::translate('Apply privacy settings') . '</td>' .
				'<td class="list_value">' .
				'<input type="radio" name="privatize_export" value="user" checked> ' . I18N::translate('Member') . '<br>' .
				'<input type="radio" name="privatize_export" value="visitor"> ' . I18N::translate('Visitor') .
				'</td></tr>';
		}

		$out .= '
		<tr><td class="descriptionbox width50 wrap">' . I18N::translate('Convert from UTF-8 to ISO-8859-1') . '</td>
		<td class="optionbox"><input type="checkbox" name="convert" value="yes"></td></tr>

		<tr><td class="topbottombar" colspan="2">
		<input type="button" class="btn btn-secondary" value="' . /* I18N: A button label. */ I18N::translate('cancel') . '" onclick="cancelDownload();">
		<input type="submit" class="btn btn-primary" value="' . /* I18N: A button label. */ I18N::translate('download') . '">
		</form>';

		return $out;
	}
}
