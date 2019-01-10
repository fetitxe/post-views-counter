<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Post_Views_Counter_Dashboard class.
 * 
 * @class Post_Views_Counter_Dashboard
 */
class Post_Views_Counter_Dashboard {

	public function __construct() {
		// actions
		add_action( 'wp_dashboard_setup', array( $this, 'wp_dashboard_setup' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
		add_action( 'wp_ajax_pvc_dashboard_chart', array( $this, 'dashboard_widget_chart' ) );
		add_action( 'wp_ajax_pvc_dashboard_crono', array( $this, 'dashboard_widget_crono' ) );
		add_action( 'wp_ajax_pvc_dashboard_crono_dynamic_post', array( $this, 'dynamic_post_autocomplete' ) );
	}

	/**
	 * Initialize widget.
	 */
	public function wp_dashboard_setup() {
		// filter user_can_see_stats
		if ( ! apply_filters( 'pvc_user_can_see_stats', current_user_can( 'publish_posts' ) ) ) {
			return;
		}
		// add dashboard widget
		wp_add_dashboard_widget( 'pvc_dashboard', __( 'Post Views', 'post-views-counter' ), array( $this, 'dashboard_widget' ) /* , array( $this, 'dashboard_widget_control' ) */ );
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return mixed
	 */
	public function dashboard_widget() {
		?>
		<div id="pvc_crono_container">
			<div class="pvc_hidden hidden" style="margin-bottom: .5rem; text-align: right;" >
				<button id="pvc_crono_chart_trigger"class="button button-primary" ><?php _e( 'Posts Evolution ', 'post-views-counter' ); ?></button>
			</div>
			<div id="pvc_crono_chart" class="hidden">
				<span class="dashicons dashicons-no closeDiv"></span>
				<h2><?php _e( 'Posts Evolution ', 'post-views-counter' ); ?></h2>
				<div class="pvc_crono_responsive">
					<div id="pvc_crono_area" class="r_chart">
						<canvas id="pvc_crono" height="100"></canvas>
					</div>
					<div class="r_leyend">
						<ul id="pvc_crono_leyend"></ul>
					</div>
					<div class="r_control">
						<form id="pvc_crono_control_form">
							<div class="form_group form-field">
								<label for="pvc_crono_post_type"><?php _e( 'Show:', 'post-views-counter' ); ?></label>
								<select id="pvc_crono_post_type" name="pvc_crono_post_type">
									<option value="total" selected><?php _e( 'Total Post Views', 'post-views-counter' ); ?></option>
									<option value="all"><?php _e( 'All Times Best Post', 'post-views-counter' ); ?></option>
									<option value="day"><?php _e( 'By Days Best Posts', 'post-views-counter' ); ?></option>
									<option value="custom"><?php _e( 'Custom Posts List', 'post-views-counter' ); ?></option>
								</select>
								<textarea placeholder="<?php _e( 'Post IDs List, comma separated...', 'post-views-counter' ); ?>" cols="33" rows="3" id="pvc_crono_post_ids" style="display:none;" name="pvc_crono_post_ids" autocomplete="off" role="combobox"></textarea>
							</div>
							<div class="form_group form-field">
								<label for="pvc_crono_post_amount"><?php _e( 'Post to show:', 'post-views-counter' ); ?></label>
								<select id="pvc_crono_post_amount" name="pvc_crono_post_amount">
									<option value="1" >1</option>
									<option value="5" selected>5</option>
									<?php
									for($i=10;$i<30;$i+=5){
										?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php 
									}
									?>
								</select>
							</div>
							<?php if( function_exists('pll_get_post_language') ){ 
								$usedLangs = pll_languages_list();
								?><div class="form_group form-field" id="pvc_crono_post_langs"><?php
								foreach( $usedLangs as $i=>$lang ){
									?><input type="checkbox" id="pvc_crono_post_lang-<?php echo $i; ?>" name="pvc_crono_post_lang[]" value="<?php echo $lang; ?>" checked> <label for="pvc_crono_post_lang-<?php echo $i; ?>"> <img src="<?php echo plugins_url().'/polylang/flags/'.$lang.'.png'; ?>" alt="<?php echo $lang; ?>-"> <?php echo $lang; ?></label><br><?php
								}
								?></div>
							<?php } ?>
							<div class="form_group form-field" style="align-items: center;display: inherit;">
								<label for="pvc_crono_post_period" style="margin-right: 4px;"><?php _e( 'Period:', 'post-views-counter' ); ?></label> 
								<input id="pvc_crono_post_period" type="text" style="width:auto;" name="pvc_crono_post_period" value="">
							</div>
							<div class="form_group form-field">
								<label for="pvc_crono_post_interval"><?php _e( 'Time interval:', 'post-views-counter' ); ?></label>
								<select id="pvc_crono_post_interval" name="pvc_crono_post_interval">
									<option value="day" selected><?php _e('Days', 'post-views-counter');?></option>
									<option value="week" ><?php _e('Weeks', 'post-views-counter');?></option>
									<option value="month" ><?php _e('Months', 'post-views-counter');?></option>
									<option value="year" ><?php _e('Years', 'post-views-counter');?></option>
								</select>
							</div>
							<div class="form_group form-field" style="align-items: center;display: inherit;">
								<button id="pvc_crono_submit"class="button button-primary" ><?php _e( 'Show it!', 'post-views-counter' ); ?></button>
								<a href="#TB_inline?&width=640&height=550&inlineId=pvc_crono_help" class="thickbox" style="margin-left: .5rem;"><?php _e('Need Help?', 'post-views-counter'); ?></a>
								<?php add_thickbox(); ?>
								<div id="pvc_crono_help" style="display:none;">
									<h3><?php _e('How it works...', 'post-views-counter'); ?></h3>
									<ul style="margin-left: 1rem;">
										<li>
											<span><b><?php _e( 'Show:', 'post-views-counter' ); ?></b></span>
											<ul style="margin-left: 1rem;">
												<li>
													<span><i><?php _e( 'Total Post Views', 'post-views-counter' ); ?></i>: <?php _e( 'Shows the total posts views for the total views count', 'post-views-counter' ); ?></span>
												</li>
												<li>
													<span><i><?php _e( 'All Times Best Post', 'post-views-counter' ); ?></i>: <?php _e( 'Shows the evolution of views for the most viewed posts in the total views count', 'post-views-counter' ); ?></span>
												</li>
												<li>
													<span><i><?php _e( 'By Days Best Posts', 'post-views-counter' ); ?></i>: <?php _e( 'Shows the evolution of most viewed posts at day by day views.', 'post-views-counter' ); ?></span>
												</li>
												<li>
													<span><i><?php _e( 'Custom Posts List', 'post-views-counter' ); ?></i>: <?php _e( 'Shows the evolution of a custom list of selected posts.', 'post-views-counter' ); ?></span>
												</li>
											</ul>
										</li>
										<li>
											<span><b><?php _e( 'Post to show:', 'post-views-counter' ); ?></b></span>
											<span><?php _e('Amount of posts to by showed.', 'post-views-counter'); ?></span>
										</li>
										<li>
											<span><b><?php _e( 'Period:', 'post-views-counter' ); ?></b></span>
											<span><?php _e('Lapse of time to retrieve views data.', 'post-views-counter'); ?></span>
										</li>
										<li>
											<span><b><?php _e( 'Time interval:', 'post-views-counter' ); ?></b></span>
											<span><?php _e('How the evolution must by showed: by day, by week, by month or by year.', 'post-views-counter'); ?></span>
										</li>
									</ul>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div id="pvc_dashboard_container">
			<canvas id="pvc_chart" height="175"></canvas>
			<div class="pvc_option pvc_hidden hidden" style="margin-top: .5rem;">
				<button class="button button-primary" data-value="this_week"><?php _e( 'This Week', 'post-views-counter' ); ?></button>
				<button class="button button-primary" data-value="this_month"><?php _e( 'This Month', 'post-views-counter' ); ?></button>
				<button class="button button-primary" data-value="this_year"><?php _e( 'This Year', 'post-views-counter' ); ?></button>
			</div>
			<div class="pvc_option pvc_hidden hidden" style="margin-top: .5rem;">
				<b><?php _e( 'Custom:', 'post-views-counter' ); ?></b> <input id="pvc_custom" type="text" class="datePicker" name="customDate" value="">
				<button id="pvc_custom_button" class="button button-primary" data-value="this_month"><?php _e( 'Show', 'post-views-counter' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Dashboard widget settings.
	 *
	 * @return mixed
	 */
	public function dashboard_widget_control() {
		
	}

	/**
	 * Dashboard widget chart data function.
	 * 
	 * @global $_wp_admin_css_colors
	 * @return mixed
	 */
	public function dashboard_widget_chart() {

		if ( ! apply_filters( 'pvc_user_can_see_stats', current_user_can( 'publish_posts' ) ) )
			wp_die( _( 'You do not have permission to access this page.', 'post-views-counter' ) );

		if ( ! check_ajax_referer( 'dashboard-chart', 'nonce' ) )
			wp_die( __( 'You do not have permission to access this page.', 'post-views-counter' ) );


		
		$post_types = Post_Views_Counter()->options['general']['post_types_count'];

		// get stats
		$query_args = array(
			'post_type'			 => $post_types,
			'posts_per_page'	 => -1,
			'paged'				 => false,
			'orderby'			 => 'post_views',
			'suppress_filters'	 => false,
			'no_found_rows'		 => true
		);

		// set range
		$range = isset( $_REQUEST['period'] ) ? esc_attr( $_REQUEST['period'] ) : 'this_month';

		// $now = getdate( current_time( 'timestamp', get_option( 'gmt_offset' ) ) );
		//$now = getdate( current_time( 'timestamp', get_option( 'gmt_offset' ) ) - 2592000 );
		$justNow = time();

		// get admin color scheme
		global $_wp_admin_css_colors;

		$admin_color = get_user_option( 'admin_color' );
		$colors = $_wp_admin_css_colors[$admin_color]->colors;
		$color = $this->hex2rgb( $colors[2] );

		// set chart labels
		switch(true){
			case ( is_numeric($range) && strlen($range)===16 ):
				$range = str_split($range, 2);
				$rangeDate['start'] = $range[2].$range[3].'-'.$range[1].'-'.$range[0];
				//$rangeDate['start'] =  new DateTime( $range[2].$range[3].'-'.$range[1].'-'.$range[0] );
				$rangeDate['end'] = $range[6].$range[7].'-'.$range[5].'-'.$range[4];
				$data = array(
					'text' => array(
						'xAxes'	 => date_i18n('j F Y', strtotime($rangeDate['start']) ).' - '.date_i18n('j F Y', strtotime($rangeDate['end']) ),
						'yAxes'	 => __( 'Post Views', 'post-views-counter' )
					),
					'design' => array(
						'fill'					 => true,
						'backgroundColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 0.2)',
						'borderColor'			 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'borderWidth'			 => 1.2,
						'borderDash'			 => array(),
						'pointBorderColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'pointBackgroundColor'	 => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth'		 => 1.2
					)
				);

				$data['data']['datasets'][0]['label'] = __( 'Total Views', 'post-views-counter' );

				// get data for specific post types
				$empty_post_type_views = array();
				
				// reindex post types
				$post_types = array_combine( range( 1, count( $post_types ) ), array_values( $post_types ) );

				$post_type_data = array();

				foreach ( $post_types as $id => $post_type ){
					$empty_post_type_views[$post_type] = 0;
					$post_type_obj = get_post_type_object( $post_type );

					$data['data']['datasets'][$id]['label'] = $post_type_obj->labels->name;
					$data['data']['datasets'][$id]['data'] = array();

					// get range views
					$fixedEnd = new DateTime($rangeDate['end']);
					$fixedEnd->add(new DateInterval('P1D'));
					$period = new DatePeriod( new DateTime($rangeDate['start']), new DateInterval('P1D'), $fixedEnd );

					$post_type_data[$id] = array();
					foreach ($period as $dt){
						$val = array_values( 
							pvc_get_views(
								array(
									'fields'		 => 'date=>views',
									'post_type'		 => $post_type,
									'views_query'	 => array(
										'year'	 => $dt->format("Y"),
										'month'	 => $dt->format("m"),
										'week'	 => '',
										'day'	 => $dt->format("d"),
									)
								)
							)
						);
						$post_type_data[$id][] = $val[0];
					}
				}

				$sum = array();

				foreach ( $post_type_data as $post_type_id => $post_views ) {
					foreach ( $post_views as $id => $views ) {
						// generate chart data for specific post types
						$data['data']['datasets'][$post_type_id]['data'][] = $views;

						if ( ! array_key_exists( $id, $sum ) )
							$sum[$id] = 0;

						$sum[$id] += $views;
					}
				}

				// this period all days
				foreach ($period as $i => $dt){
					$data['data']['labels'][] = $dt->format("D j/n/y");
					$data['data']['dates'][] = date_i18n( get_option( 'date_format' ), strtotime($dt->format("Y-m-d")) );
					$data['data']['datasets'][0]['data'][] = $sum[$i];
				}
				break;

			case ($range === 'this_week'):
				$data = array(
					'text' => array(
						'xAxes'	 => date_i18n('j F Y',$justNow -(86400*( date('N',$justNow) - 1 ))).' - '.date_i18n('j F Y',$justNow -(86400*( date('N',$justNow) - 7 ))),
						'yAxes'	 => __( 'Post Views', 'post-views-counter' )
					),
					'design' => array(
						'fill'					 => true,
						'backgroundColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 0.2)',
						'borderColor'			 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'borderWidth'			 => 1.2,
						'borderDash'			 => array(),
						'pointBorderColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'pointBackgroundColor'	 => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth'		 => 1.2
					)
				);

				$data['data']['datasets'][0]['label'] = __( 'Total Views', 'post-views-counter' );

				// get data for specific post types
				$empty_post_type_views = array();
				
				// reindex post types
				$post_types = array_combine( range( 1, count( $post_types ) ), array_values( $post_types ) );

				$post_type_data = array();

				foreach ( $post_types as $id => $post_type ) {
					$empty_post_type_views[$post_type] = 0;
					$post_type_obj = get_post_type_object( $post_type );

					$data['data']['datasets'][$id]['label'] = $post_type_obj->labels->name;
					$data['data']['datasets'][$id]['data'] = array();

					// get month views
					$post_type_data[$id] = array_values(
						pvc_get_views(
							array(
								'fields'		 => 'date=>views',
								'post_type'		 => $post_type,
								'views_query'	 => array(
									'year'	 => date( 'Y' ),
									'month'	 => date( 'm' ),
									'week'	 => date( 'W' ),
									'day'	 => ''
								)
							)
						)
					);
				}

				$sum = array();

				foreach ( $post_type_data as $post_type_id => $post_views ) {
					foreach ( $post_views as $id => $views ) {
						// generate chart data for specific post types
						$data['data']['datasets'][$post_type_id]['data'][] = $views;

						if ( ! array_key_exists( $id, $sum ) )
							$sum[$id] = 0;

						$sum[$id] += $views;
					}
				}

				// this week all days
				for ( $i = 1; $i <= 7; $i ++ ) {
					$date = date('j M', $justNow -(86400*( date('N',$justNow) - $i )));
					$data['data']['labels'][] = $date;
					$data['data']['dates'][] = date_i18n( get_option( 'date_format' ), strtotime($date) );
					$data['data']['datasets'][0]['data'][] = $sum[$i - 1];
				}
				break;

			case ($range === 'this_year'):
				$data = array(
					'text'	 => array(
						//'xAxes'	 => __( 'Year', 'post-views-counter' ) . date( ' Y' ),
						'xAxes'	 => date( ' Y' ),
						'yAxes'	 => __( 'Post Views', 'post-views-counter' ),
					),
					'design' => array(
						'fill'					 => true,
						'backgroundColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 0.2)',
						'borderColor'			 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'borderWidth'			 => 1.2,
						'borderDash'			 => array(),
						'pointBorderColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'pointBackgroundColor'	 => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth'		 => 1.2
					)
				);

				$data['data']['datasets'][0]['label'] = __( 'Total Views', 'post-views-counter' );

				// get data for specific post types
				$empty_post_type_views = array();

				// reindex post types
				$post_types = array_combine( range( 1, count( $post_types ) ), array_values( $post_types ) );

				$post_type_data = array();

				foreach ( $post_types as $id => $post_type ) {
					$empty_post_type_views[$post_type] = 0;
					$post_type_obj = get_post_type_object( $post_type );

					$data['data']['datasets'][$id]['label'] = $post_type_obj->labels->name;
					$data['data']['datasets'][$id]['data'] = array();

					// get month views
					$post_type_data[$id] = array_values(
						pvc_get_views(
							array(
								'fields'		 => 'date=>views',
								'post_type'		 => $post_type,
								'views_query'	 => array(
									'year'	 => date( 'Y' ),
									'month'	 => '',
									'week'	 => '',
									'day'	 => ''
								)
							)
						)
					);
				}

				$sum = array();

				foreach ( $post_type_data as $post_type_id => $post_views ) {
					foreach ( $post_views as $id => $views ) {
						// generate chart data for specific post types
						$data['data']['datasets'][$post_type_id]['data'][] = $views;

						if ( ! array_key_exists( $id, $sum ) )
							$sum[$id] = 0;

						$sum[$id] += $views;
					}
				}

				// this year all months
				for ( $i = 1; $i <= 12; $i ++ ) {
					// generate chart data
					$data['data']['labels'][] = $i;
					$data['data']['dates'][] = date_i18n( 'F Y', strtotime( date( 'Y' ) . '-' . str_pad( $i, 2, '0', STR_PAD_LEFT ) . '-01' ) );
					$data['data']['datasets'][0]['data'][] = $sum[$i - 1];
				}
				break;

			case ($range === 'this_month'):
			default :
				$data = array(
					'text'	 => array(
						'xAxes'	 => '1-'.date('t').' '.date_i18n( 'F Y' ),
						'yAxes'	 => __( 'Post Views', 'post-views-counter' ),
					),
					'design' => array(
						'fill'					 => true,
						'backgroundColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 0.2)',
						'borderColor'			 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'borderWidth'			 => 1.2,
						'borderDash'			 => array(),
						'pointBorderColor'		 => 'rgba(' . $color['r'] . ',' . $color['g'] . ',' . $color['b'] . ', 1)',
						'pointBackgroundColor'	 => 'rgba(255, 255, 255, 1)',
						'pointBorderWidth'		 => 1.2
					)
				);

				$data['data']['datasets'][0]['label'] = __( 'Total Views', 'post-views-counter' );

				// get data for specific post types
				$empty_post_type_views = array();

				// reindex post types
				$post_types = array_combine( range( 1, count( $post_types ) ), array_values( $post_types ) );

				$post_type_data = array();

				foreach ( $post_types as $id => $post_type ) {
					$empty_post_type_views[$post_type] = 0;
					$post_type_obj = get_post_type_object( $post_type );

					$data['data']['datasets'][$id]['label'] = $post_type_obj->labels->name;
					$data['data']['datasets'][$id]['data'] = array();

					// get month views
					$post_type_data[$id] = array_values(
						pvc_get_views(
							array(
								'fields'		 => 'date=>views',
								'post_type'		 => $post_type,
								'views_query'	 => array(
									'year'	 => date( 'Y' ),
									'month'	 => date( 'm' ),
									'week'	 => '',
									'day'	 => ''
								)
							)
						)
					);
				}

				$sum = array();

				foreach ( $post_type_data as $post_type_id => $post_views ) {
					foreach ( $post_views as $id => $views ) {
						// generate chart data for specific post types
						$data['data']['datasets'][$post_type_id]['data'][] = $views;

						if ( ! array_key_exists( $id, $sum ) )
							$sum[$id] = 0;

						$sum[$id] += $views;
					}
				}

				// this month all days
				for ( $i = 1; $i <= date( 't' ); $i ++ ) {
					// generate chart data
					$theDay = strtotime( date('Y') . '-' . date('m') . '-' . str_pad( $i, 2, '0', STR_PAD_LEFT) );
					$data['data']['labels'][] = ( $i % 2 === 0 ? '' : date_i18n( 'j D', $theDay) );
					$data['data']['dates'][] = date_i18n( get_option('date_format'), $theDay);
					$data['data']['datasets'][0]['data'][] = $sum[$i - 1];
				}
				break;
		}

		echo json_encode($data);
		exit;
	}

	/**
	 * Dashboard widget crono data function.
	 * 
	 * @global $_wp_admin_css_colors
	 * @global $wpdb
	 * @return mixed
	 */
	public function dashboard_widget_crono() {
		if ( ! apply_filters( 'pvc_user_can_see_stats', current_user_can( 'publish_posts' ) ) ){
			wp_die( _( 'You do not have permission to access this page.', 'post-views-counter' ) );
		}
		if ( ! check_ajax_referer( 'dashboard-chart', 'nonce' ) ){
			wp_die( __( 'You do not have permission to access this page.', 'post-views-counter' ) );
		}

		global $wpdb;

		$request;
		parse_str($_REQUEST['opts'], $request);
		//$request['pvc_crono_post_type'];
		//$request['pvc_crono_post_amount'];
		//$request['pvc_crono_post_lang'];
		//$request['pvc_crono_post_period'];
		//$request['pvc_crono_post_interval'];
		//$request['pvc_crono_post_ids'];

		// Get dates in unix time
		$cloneToLine = false;
		$periodDates = explode (' - ', $request['pvc_crono_post_period']);
		$periodDates[0] = new DateTime($periodDates[0]);
		$periodDates[1] = new DateTime($periodDates[1]);

		$data = array(
			'text' => array(
				'xAxes'		 => date_i18n('j F Y', $periodDates[0]->getTimestamp()) . ' - ' . date_i18n('j F Y', $periodDates[1]->getTimestamp()),
				'yAxes'		 => __( 'Post Views', 'post-views-counter' )
			),
			'design' => array(
				'fill'					 => false,
				'borderWidth'			 => 1.2,
				'borderDash'			 => array(),
				'pointBorderWidth'		 => 1.2
			)
		);

		// Adding one day at selected end to include last day in period & set it
		$periodDates[1]->add(new DateInterval('P1D'));
		$period = new DatePeriod( $periodDates[0], new DateInterval('P1D'), $periodDates[1] );
		$cloneToLine = iterator_count($period) === 1 ? true : false ;

		// Define the post list to show
		$selected_post = array();

		if( $request['pvc_crono_post_type'] == 'day' ){
			// Best post day by day
			foreach( $period as $current){
				$selected = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."post_views WHERE period = '".$current->format("Ymd")."' ORDER BY count DESC", ARRAY_A, 0 );
				$counter = 0;
				foreach( $selected as $post){
					if( function_exists('pll_get_post_language') ){
						if( in_array(pll_get_post_language($post['id']),$request['pvc_crono_post_lang']) ){
							$selected_post[] = $post['id'];
							$counter++;
						}
					}else{
						$selected_post[] = $post['id'];
						$counter++;
					}
					if( $counter >= $request['pvc_crono_post_amount'] ){
						break;
					}
				}
			}

			$selected_post = array_unique($selected_post, SORT_NUMERIC);

		}elseif( $request['pvc_crono_post_type'] == 'custom'){
			foreach( explode(',',$request['pvc_crono_post_ids']) as $inputs){
				$get = $this->auto_extract_preg_match($inputs);
				if ( isset($get[1]) && is_numeric($get[1]) ){
					$selected_post[] = $get[1];
				}
			}
			$selected_post = array_unique($selected_post, SORT_NUMERIC);

		}elseif( $request['pvc_crono_post_type'] == 'all'){
			// All times best post
			$posts = pvc_get_most_viewed_posts(
				array(
					'posts_per_page'	=> $request['pvc_crono_post_amount'],
					'order'				=> 'desc',
					'lang'				=> $request['pvc_crono_post_lang']
				)
			);
			if( !empty($posts) ){
				foreach( $posts as $i=>$post ){
					setup_postdata($post);
					$selected_post[] = $post->ID;
				}
				wp_reset_postdata();
			}
		}else{
			// Total of
			$selected_post[] = 'total';
		}

		// Order posts array indexes
		$selected_post = array_values($selected_post);

		// Get views data from selected posts
		foreach( $selected_post as $i=>$post_id ){
			$data['data']['datasets'][$i]['label'] = $post_id !== 'total' ? get_the_title($post_id).'('.pvc_get_post_views($post_id).')' : __('Total','post-views-counter');
			$data['data']['datasets'][$i]['post_id'] = $post_id;
			$data['data']['datasets'][$i]['data'] = array();

			$color = $this->hex2rgb(dechex(rand(0x000000, 0xFFFFFF)));
			$data['data']['datasets'][$i]['backgroundColor'] = 'rgba('.$color['r'].','.$color['g'].','.$color['b'].',0.001)';
			$data['data']['datasets'][$i]['borderColor'] = 'rgba('.$color['r'].','.$color['g'].','.$color['b'].',1)';
			$data['data']['datasets'][$i]['pointBorderColor'] = 'rgba('.$color['r'].','.$color['g'].','.$color['b'].',0.3)';
			$data['data']['datasets'][$i]['pointBackgroundColor'] = 'rgba('.$color['r'].','.$color['g'].','.$color['b'].',1)';

			$s_laps = array(
				'day'	=> 'P1D',
				'week'	=> 'P1W',
				'month'	=> 'P1M',
				'year'	=> 'P1Y'
			);
			$s_period = new DatePeriod( $periodDates[0], new DateInterval( $s_laps[ $request['pvc_crono_post_interval'] ] ), $periodDates[1] );

			foreach( $s_period as $date_current ){
				$data['data']['datasets'][$i]['data'][] = pvc_get_views(
					array(
						'fields'		=> 'views',
						'post_id'		=> $post_id,
						'post_type'		=> Post_Views_Counter()->options['general']['post_types_count'],
						'views_query'	=> array(
							'year'		=> $date_current->format('Y'),
							'month'		=> ( $request['pvc_crono_post_interval']=='month' || $request['pvc_crono_post_interval']=='day' )? $date_current->format('m') : '',
							'week'		=> $request['pvc_crono_post_interval']=='week' ? $date_current->format('W') : '',
							'day'		=> $request['pvc_crono_post_interval']=='day' ? $date_current->format('d') : '',
						)
					)
				);
			}
		}

		// Get label points for the data
		$s_label = array(
			'day'	=> 'j-m-Y',
			'week'	=> 'M Y (W)',
			'month'	=> 'M Y',
			'year'	=> 'Y'
		);
		$s_tooltip = array(
			'day'	=> 'l, j F Y',
			'week'	=> '<W> M Y',
			'month'	=> 'F Y',
			'year'	=> 'Y'
		);
		foreach( $s_period as $date_current ){
			$data['data']['labels'][] = date_i18n( $s_label[$request['pvc_crono_post_interval']], $date_current->getTimestamp());
			$data['data']['dates'][] = date_i18n( $s_tooltip[$request['pvc_crono_post_interval']], $date_current->getTimestamp());
		}

		// Draw single days as line
		if( $cloneToLine ){
			foreach( $selected_post as $i=>$post_id ){
				$data['data']['datasets'][$i]['data'][] = $data['data']['datasets'][$i]['data'][0];
			}
			$data['data']['labels'][] = $data['data']['labels'][0];
			$data['data']['dates'][] = $data['data']['dates'][0];
		}

		echo json_encode($data);
		exit;
	}

	/**
	 * Enqueue admin scripts and styles.
	 * 
	 * @param string $pagenow
	 */
	public function admin_scripts_styles( $pagenow ) {
		if ( $pagenow != 'index.php' )
			return;

		// filter user_can_see_stats
		if ( ! apply_filters( 'pvc_user_can_see_stats', current_user_can( 'publish_posts' ) ) ) {
			return;
		}

		wp_register_style(
		'pvc-admin-daterangepicker', POST_VIEWS_COUNTER_URL . '/css/daterangepicker.css'
		);
		wp_enqueue_style( 'pvc-admin-daterangepicker' );

		wp_register_style(
		'pvc-admin-dashboard', POST_VIEWS_COUNTER_URL . '/css/admin-dashboard.css'
		);
		wp_enqueue_style( 'pvc-admin-dashboard' );

		wp_register_script(
		'pvc-admin-dashboard', POST_VIEWS_COUNTER_URL . '/js/admin-dashboard.js', array( 'jquery', 'pvc-chart', 'pvc-daterangepicker'), Post_Views_Counter()->defaults['version'], true
		);

		wp_register_script(
		'pvc-chart', POST_VIEWS_COUNTER_URL . '/js/chart.min.js', array( 'jquery' ), Post_Views_Counter()->defaults['version'], true
		);
		

		wp_register_script(
		'pvc-moment', POST_VIEWS_COUNTER_URL . '/js/moment.min.js', array(), Post_Views_Counter()->defaults['version'], true
		);

		wp_register_script(
		'pvc-daterangepicker', POST_VIEWS_COUNTER_URL . '/js/daterangepicker.js', array( 'pvc-moment', 'jquery' ), Post_Views_Counter()->defaults['version'], true
		);

		// set ajax args
		$ajax_args = array(
			'ajaxURL'	 => admin_url( 'admin-ajax.php' ),
			'nonce'		 => wp_create_nonce( 'dashboard-chart' )
		);

		wp_enqueue_script( 'suggest' );
		wp_enqueue_script( 'pvc-admin-dashboard' );
		// wp_enqueue_script( 'pvc-chart' );

		wp_localize_script(
		'pvc-admin-dashboard', 'pvcArgs', $ajax_args
		);
	}

	/**
	 * Convert hex to rgb color.
	 * 
	 * @param type $color
	 * @return boolean or array
	 */
	public function hex2rgb($color){
		if( !is_string($color) ){
			if( is_array($color) ){
				$color = implode($color);
			}elseif( is_numeric($color) ){
				$color .= '#'.$color;
			}
		}

		if( $color[0] == '#' ){
			$color = substr($color, 1);
		}
		if( strlen($color) == 6 ){
			list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
		}elseif( strlen($color) == 3 ){
			list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
		}else{
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return array('r' => $r, 'g' => $g, 'b' => $b);
	}


	/**
	 * Autocomplete custom post list.
	 * 
	 * @param type $s
	 * @return array
	 */
	function dynamic_post_autocomplete(){

		$s = wp_unslash( $_GET['q'] );
		$comma = _x( ',', 'page delimiter' );

		if ( ',' !== $comma ) $s = str_replace( $comma, ',', $s );

		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[count( $s ) - 1];
		}
		$s = trim( $s );

		$term_search_min_chars = 2;

		$the_query = new WP_Query( 
			array( 
				's' => $s,
				'posts_per_page' => 5,
				'post_type' => 'post',
				//'lang' => array('es','pt')
				'lang' => 'es,pt'
			) 
		);

		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$results[] = get_the_title().' ['.get_the_ID().']';
			}
			// Restore original Post Data 
			wp_reset_postdata();
		} else {
			$results = 'No results';
		}

		echo join( $results, "\n" );
		wp_die();
	}

	/**
	 * Autoextrant conten fron string.
	 * 
	 * @param strings
	 * @return array
	 */
	function auto_extract_preg_match( $str, $tag_start = '[', $tag_end = ']', $delimiter = '#' ){
		$regex = $delimiter . preg_quote($tag_start, $delimiter) . '(.*?)' . preg_quote($tag_end, $delimiter) . $delimiter . 's';
		preg_match($regex, $str, $out);
		return $out;
	}
}
