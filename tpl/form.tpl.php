<?php
global $showOn, $positionType, $displayTypes;
?>


<div class='post-form'>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(["_setAccount", "UA-33664506-1"]);
		_gaq.push(["_trackPageview"]);
		(function () {
			var ga = document.createElement("script");
			ga.type = "text/javascript";
			ga.async = true;
			ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
			var s = document.getElementsByTagName("script")[0];
			s.parentNode.insertBefore(ga, s);
		})();
	</script>
	<h1><a href="http://po.st"
	       class="post-home"></a> <?php _e( 'Po.st options', 'po.st' ); ?></h1>



	<form action='' method='post' id='post_form'>
		<input type='hidden' name='post_action' id='post_action' value='save'/>
		<?php wp_nonce_field( 'update-po.st-settings' ); ?>
		<div
			class='post-form__pubkey_error'><?php _e( 'Mandatory "Publisher Key" field cannot be left blank.', 'po.st' ); ?></div>

		<div class="post-section">
			<h2 class="post-form__heading">
				<strong><?php _e( 'Publisher Key', 'po.st' ); ?></strong>
				<small>(mandatory)</small>
			</h2>

			<div class="post-form__pubkey">
				<input type='text' name='p_key' id='p_key'
				       value='<?php echo $p_key ?>' class="regular-text"/>
			</div>
		</div>


		<?php if ( empty( $p_key ) ) : ?>
			<div class="post-form__pubkey-content">
				<?php _e( '<p>A publisher key is mandatory. The plugin will not work without it. If you don\'t have this key yet, <a href="http://www.po.st/portal/register" target="_blank">register at Po.st</a>. It will only take a minute.</p><p>If you already have a Po.st account, you can find your publisher key by <a href="http://www.po.st/portal/dashboard" target="_blank">signing in to your dashboard</a> and clicking on the administration tab at the top.</p>', 'po.st' ); ?>
			</div>
		<?php else : ?>

				<h2 class="post-form__heading">
					<strong><?php _e( 'Sharing Button Type', 'po.st' ); ?></strong>
				</h2>

				<?php if ( empty( $display_types ) ) : ?>
					<div class="post-section">
						<div class="post-form__pubkey-content">
							<?php _e( '<p><a href="http://www.po.st/portal/dashboard" target="_blank">Signin in to your dashboard</a>, activate any widget and click on on "Save Changes".</p>', 'po.st' ); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="post-section">

						<div class="post-form__type">
							<div class="post-form__pubkey-content">
								<?php _e( '<p>Choose Standard or Native button type. Selected buttons can be customized under the Social Tools tab of your <a href="http://www.po.st/portal/dashboard" target="_blank">Po.st Sharing Dashboard</a>.</p>', 'po.st' ); ?>
							</div>

							<ul id="post-list-widgets">
								<?php foreach ( $display_types as $k ): ?>
									<li data-id='display_type_<?php echo $k; ?>'>
										<input type='radio'
										       id='display_type_<?php echo $k; ?>'
										       name='display_type'
										       value='<?php echo $k; ?>' <?php if ( $k == $display_type ) {
											echo 'checked';
										} ?>/>
										<label for='display_type_<?php echo $k; ?>'><?php echo $displayTypes[$k]; ?></label>
									</li>
								<?php endforeach ?>
							</ul>

							<p>
								<button type="button" name="button" class="button-secondary" id="post-plugin-refresh">Refresh Options</button>
								<br>
								<span class="description" style="padding-top:5px; display:inline-block"><?php esc_attr_e( 'If you make changes in you enable or disable the Standard or Native sharing buttons in your dashboard click the Refresh button to update your options.', 'wp_admin_style' ); ?></span>
							</p>

						</div>


					</div>

					<div class="post-section">
							<h2 class="post-form__heading">
								<strong><?php _e( 'Include buttons on the following post types', 'po.st' ); ?></strong>
							</h2>

							<div class="post-form__showwidgets">
								<ul>
									<?php foreach ( $showOn as $k => $name ): ?>
										<li>
											<input type='checkbox'
											       id='show_on_<?php echo $k; ?>'
											       name='show_on[<?php echo $k; ?>]'
											       value='1' <?php if ( in_array( $k, $display_pages ) ) {
												echo 'checked';
											} ?>/>
											<label for='show_on_<?php echo $k; ?>'>
												<?php echo $name; ?>
											</label>
										</li>
									<?php endforeach ?>
								</ul>
							</div>
					</div>
					<div class="post-section">
						<h2 class="post-form__heading">
							<strong><?php _e( 'Button position on articles', 'po.st' ); ?></strong>
						</h2>

						<div class="post-form__position">
							<ul>
								<?php foreach ( $positionType as $k => $name ): ?>
									<li>
										<input type='checkbox'
										       id='display_position_<?php echo $k; ?>'
										       name='display_position[<?php echo $k; ?>]'
										       value='1' <?php if ( is_array( $display_position ) && in_array( $k, $display_position ) ) {
											echo 'checked';
										} ?>/>
										<label for='display_position_<?php echo $k; ?>'>
											<?php echo $name; ?>
										</label>
									</li>
								<?php endforeach ?>
							</ul>
						</div>
					</div>

			<?php endif; ?>
		<?php endif; ?>

		<input type="submit" value="<?php _e( 'Save changes', 'po.st' ); ?>"
		       class="button-primary"/>
	</form>
</div>
