<?php /* VIVA NOTE: One property form handles both create and edit. Existing core details lock after 15 minutes. */ $editing=(bool)$property;$id=$editing?(int)$property['property_id']:0;$canEdit=!$editing||($open??false); ?>
<div class="toolbar"><p><?= $editing?'Core details can only be changed during the first 15 minutes.':'Add your boarding house details; you can correct them for 15 minutes after saving.' ?></p><a href="<?=e(url('/properties'))?>">← Properties</a></div>
<?php if(!$canEdit):?><div class="panel"><h3>Property details are locked</h3><p>The 15-minute edit window has passed. You can still manage photos, facilities and verification.</p><a class="btn" href="<?=e(url('/property/show',['id'=>$id]))?>">Back to property</a></div>
<?php else:?>
<form class="panel form-panel" method="post" action="<?=e(url($editing?'/property/update':'/property/save',$editing?['id'=>$id]:[]))?>">
<?=csrf_field()?>
<div class="form-grid"><label>Boarding house name<input name="title" maxlength="100" minlength="3" required value="<?=e($property['title']??'')?>"></label>
<label>City <span class="hint">Select where the boarding house is located</span>
<select name="city_id" required>
<option value="">Select a city</option>
<?php foreach (($cities ?? []) as $city): ?>
<option value="<?= e((string)$city['id']) ?>" <?= (string)($property['city_id'] ?? '') === (string)$city['id'] ? 'selected' : '' ?>><?= e($city['name'] . ' — ' . $city['district']) ?></option>
<?php endforeach; ?>
</select></label>
<label class="full">Google Maps location link <span class="hint">Open Google Maps, select the property pin, tap Share, then Copy link. No coordinates needed.</span>
<input name="maps_url" type="url" inputmode="url" required maxlength="500" value="<?=e($property['maps_url']??'')?>" placeholder="https://maps.app.goo.gl/..." autocomplete="off">
<small class="hint">Accepts HTTPS Google Maps share links (including maps.app.goo.gl). A short link can open the map but cannot by itself supply GPS coordinates for geofencing.</small>
<?php if($editing && !empty($property['maps_url'])): ?>
<a href="<?=e($property['maps_url'])?>" target="_blank" rel="noopener noreferrer">Preview saved location ↗</a>
<?php endif; ?></label>
<label class="full">Full property address<textarea name="address" minlength="5" maxlength="1000" required rows="3"><?=e($property['address']??'')?></textarea></label>

<label class="full">Description<textarea name="description" maxlength="3000" rows="4"><?=e($property['description']??'')?></textarea></label></div>
<div class="form-actions"><button class="btn"><?= $editing?'Save changes':'Create property' ?></button><a class="btn secondary" href="<?=e(url('/properties'))?>">Cancel</a></div></form>
<?php endif;?>
