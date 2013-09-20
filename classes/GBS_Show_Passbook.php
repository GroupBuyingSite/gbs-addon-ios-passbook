<?php

/**
* Send the Passbook to the device
*/
class GBS_Show_Passbook {
	const QUERY_VAR = 'passbook';

	public function init() {
		add_action( 'gb_voucher_pre_header', array( __CLASS__, 'maybe_create_passbook' ) );
		add_action( 'gb_voucher_head', array( __CLASS__, 'inject_voucher_head' ) );
		add_action( 'gb_voucher_footer', array( __CLASS__, 'inject_voucher_footer' ) );
	}

	public function maybe_create_passbook() {
		if ( isset( $_GET[self::QUERY_VAR] ) && $_GET[self::QUERY_VAR] ) {
			GBS_Create_Passbook::pass( get_the_id() );
		}
	}

	public function inject_voucher_head() {
		global $is_iphone;
		if ( self::is_passbook_ready() ) {
			?>
				<script type="text/javascript">
					<!--
						window.printpage = function() {
							return false;
						}
						function hide_pb_modal(){
							document.getElementById('passbook_modal').style.display = 'none';
						}
					//-->
				</script>
				<style type="text/css">
					#passbook_modal {
						position:fixed;
						top:0;
						left:0;
						width:100%;
						height:100%;
						background-color:rgba(0,0,0,0.8);
						text-align:center;
						z-index:101;
					}
					#passbook_links {
						position: absolute;
						top: 8%;
						left: 8%;
						background-color: #F6F6F6;
						padding: 20px;
						border-radius: 5px;
						box-shadow: 0px 0px 5px #F6F6F6;
						border: 1px solid #FFF;
						zoom: 250%;
					}
					#passbook_links a {
						-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
						-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
						box-shadow:inset 0px 1px 0px 0px #ffffff;
						background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
						background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
						filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
						background-color:#ededed;
						-moz-border-radius:6px;
						-webkit-border-radius:6px;
						border-radius:6px;
						border:1px solid #dcdcdc;
						display:inline-block;
						color:#777777;
						font-family:arial;
						font-size:15px;
						font-weight:bold;
						padding:6px 24px;
						text-decoration:none;
						text-shadow:1px 1px 0px #ffffff;
					}
					a#passbook_download  {
						-moz-box-shadow:inset 0px 1px 0px 0px #bbdaf7;
						-webkit-box-shadow:inset 0px 1px 0px 0px #bbdaf7;
						box-shadow:inset 0px 1px 0px 0px #bbdaf7;
						background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #79bbff), color-stop(1, #378de5) );
						background:-moz-linear-gradient( center top, #79bbff 5%, #378de5 100% );
						filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#79bbff', endColorstr='#378de5');
						background-color:#79bbff;
						border:1px solid #84bbf3;
						color:#ffffff;
						text-shadow:1px 1px 0px #528ecc;
						margin-bottom: 15px;
					}
					@media print {
						#passbook_link { display: none; }
					}
				</style>
			<?php
		}
	}

	public function is_passbook_ready() {
		global $is_iphone;
		$is_mac = (strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false );
		if ( $is_iphone || $is_mac ) {
			return TRUE;
		}
		return FALSE;
	}

	public function inject_voucher_footer() {
		?>
			<div id="passbook_modal" class="clearfix">
				<div id="passbook_links" class="clearfix">
					<a href="<?php echo add_query_arg( array( self::QUERY_VAR => 1 ) ) ?>" id="passbook_download"><?php gb_e( 'Download Passbook') ?></a>
					<br/>
					<a href="javascript:hide_pb_modal();"><?php gb_e( 'No Thanks') ?></a>
				</div><!-- #passbook_link -->
			</div><!-- #passbook_modal -->
		<?php
	}
}