<?php use Fisharebest\Webtrees\I18N; ?>

<h2><?= $title ?></h2>

<form method="post" action="<?= e(route('contact')) ?>">
	<?= csrf_field() ?>
	<input type="hidden" name="ged" value="<?= e($tree->getName()) ?>">
	<input type="hidden" name="url" value="<?= e($url) ?>">

	<div class="form-group row">
		<label class="col-sm-3 col-form-label" for="to">
			<?= I18N::translate('To') ?>
		</label>
		<div class="col-sm-9">
			<input type="hidden" name="to" value="<?= e($to) ?>">
			<input class="form-control" id="to" type="text" value="<?= e($to_name) ?>" disabled>
		</div>
	</div>

	<div class="form-group row">
		<label class="col-sm-3 col-form-label" for="from-name">
			<?= I18N::translate('Your name') ?>
		</label>
		<div class="col-sm-9">
			<input class="form-control" id="from-name" type="text" name="from_name" value="<?= e($from_name) ?>" required>
		</div>
	</div>
	<div class="form-group row">
		<label class="col-sm-3 col-form-label" for="from-email">
			<?= I18N::translate('Email address') ?>
		</label>
		<div class="col-sm-9">
			<input class="form-control" id="from-email" type="email" name="from_email" value="<?= e($from_email) ?>" required>
		</div>
	</div>

	<div class="form-group row">
		<label class="col-sm-3 col-form-label" for="subject">
			<?= I18N::translate('Subject') ?>
		</label>
		<div class="col-sm-9">
			<input class="form-control" id="subject" type="text" name="subject" value="<?= e($subject) ?>" required>
		</div>
	</div>

	<div class="form-group row">
		<label class="col-sm-3 col-form-label" for="body">
			<?= I18N::translate('Message') ?>
		</label>
		<div class="col-sm-9">
			<textarea class="form-control" id="body" rows="5" type="text" name="body" required><?= e($body) ?></textarea>
		</div>
	</div>

	<div class="form-group row">
		<div class="col-sm-3 col-form-label"></div>
		<div class="col-sm-9">
			<button type="submit" class="btn btn-primary">
				<?= I18N::translate('Send') ?>
			</button>
			<a class="btn btn-link" href="<?= e($url) ?>">
				<?= I18N::translate('cancel') ?>
			</a>
		</div>
	</div>
</form>
