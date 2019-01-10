( function ( $ ) {

	// set global options
	// Chart.defaults.global.tooltips.titleMarginBottom = 0;
	// Chart.defaults.global.tooltips.footerMarginTop = 4;

	window.onload = function () {
		updateChart( 'this_month' );
		$('.pvc_hidden').show();
	};

	$('#pvc_custom').daterangepicker({
		ranges: {
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 14 Days': [moment().subtract(13, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'Last 60 Days': [moment().subtract(59, 'days'), moment()],
			'Last 90 Days': [moment().subtract(89, 'days'), moment()],
			'Last 365 Days': [moment().subtract(364, 'days'), moment()],
		},
		opens: "center",
		locale: {
			format: "DD/MM/YYYY",
		},
		maxDate: moment(),
	}, function(start, end, label) {
		$('#pvc_custom_button').data('value', start.format('DDMMYYYY')+end.format('DDMMYYYY'));
	});

	$('.pvc_option button').click(function(){
		removeData();
		updateChart($(this).data('value'));
	});

	function updateChart( period ) {

		var container = document.getElementById( 'pvc_dashboard_container' );

		if ( $( container ).length > 0 ) {

			$( container ).addClass( 'loading' ).append( '<span class="spinner is-active"></span>' );

			$.ajax( {
				url: pvcArgs.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: ( {
					action: 'pvc_dashboard_chart',
					nonce: pvcArgs.nonce,
					period: period
				} ),
				success: function ( args ) {
					$( container ).removeClass( 'loading' );
					$( container ).find( '.spinner' ).removeClass( 'is-active' );

					var config = {
						type: 'line',
						data: args.data,
						options: {
							responsive: true,
							legend: {
								display: false,
								position: 'bottom',
							},
							scales: {
								xAxes: [ {
										display: true,
										scaleLabel: {
											display: true,
											labelString: args.text.xAxes,
											fontSize: 14,
											fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif'
										}
									} ],
								yAxes: [ {
										display: true,
										scaleLabel: {
											display: false,
											labelString: args.text.yAxes
										}
									} ]
							},
							hover: {
								mode: 'label'
							},
							tooltips: {
								custom: function ( tooltip ) {
									// console.log( tooltip );
								},
								callbacks: {
									title: function ( tooltip ) {
										return args.data.dates[tooltip[0].index];
									}
								},
								mode: 'point'
							},
							events: ['click']
						}
					};

					$.each( config.data.datasets, function ( i, dataset ) {
						dataset.fill = args.design.fill;
						dataset.borderColor = args.design.borderColor;
						dataset.backgroundColor = args.design.backgroundColor;
						dataset.borderWidth = args.design.borderWidth;
						dataset.borderDash = args.design.borderDash;
						dataset.pointBorderColor = args.design.pointBorderColor;
						dataset.pointBackgroundColor = args.design.pointBackgroundColor;
						dataset.pointBorderWidth = args.design.pointBorderWidth;
					} );

					window.chartPVC = new Chart( document.getElementById( 'pvc_chart' ).getContext( '2d' ), config );
				}
			} );

		}
	}

	function removeData(){
		if (typeof window.chartPVC !== 'undefined') {
			//console.log('Clean!!');
			window.chartPVC.data.labels.pop();
			window.chartPVC.data.datasets.forEach((dataset) => {
				dataset.data.pop();
			});
			window.chartPVC.update();
		}
	}

	function updateLegend() {
		$legendContainer = $( '#legendContainer' );
		$legendContainer.empty();
		$legendContainer.append( window.chartPVC.generateLegend() );
	}

	$('#pvc_crono_chart_trigger').click(function(){
		$('#pvc_crono_chart').show();
		updateCrono();
	});
	$('#pvc_crono_chart .closeDiv').click(function(){
		$('#pvc_crono_chart').hide();
	});

	$('#pvc_crono_submit').click(function(event){
		event.preventDefault();
		removeData();
		updateCrono();
	});

	$('#pvc_crono_post_period').daterangepicker({
		startDate: moment().subtract(6, 'days'),
		endDate: moment(),
		ranges: {
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 14 Days': [moment().subtract(13, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'Last 60 Days': [moment().subtract(59, 'days'), moment()],
			'Last 90 Days': [moment().subtract(89, 'days'), moment()],
			'Last 365 Days': [moment().subtract(364, 'days'), moment()],
			'Last 730 Days': [moment().subtract(729, 'days'), moment()],
		},
		opens: "center",
		drops: "up",
		parentEl: "#pvc_crono_chart",
		locale: {
			format: "YYYYMMDD",
		},
		maxDate: moment(),
	});

	$('#pvc_crono_post_type').change(function(e){
		if( $(this).val() == 'custom'){
			$('#pvc_crono_post_ids').show();
		}else if( $(this).val() == 'total' ){
			$('#pvc_crono_post_langs').hide();
		}else{
			$('#pvc_crono_post_ids').hide();
			$('#pvc_crono_post_langs').show();
		}
	});
	$('#pvc_crono_post_ids').suggest( window.ajaxurl + "?action=pvc_dashboard_crono_dynamic_post", {multiple:true, multipleSep: ","});

	function updateCrono(){
		var container = document.getElementById( 'pvc_crono_area' );
		if( $(container).length > 0 ){
			$(container).addClass('loading').append('<span class="spinner is-active"></span>');
			$.ajax({
				url: pvcArgs.ajaxURL,
				type: 'POST',
				dataType: 'json',
				data: ({
					action: 'pvc_dashboard_crono',
					nonce: pvcArgs.nonce,
					opts: $('#pvc_crono_control_form').serialize()
				}),
				success: function(args){
					$(container).removeClass('loading');
					$(container).find('.spinner').removeClass('is-active');
					var config = {
						type: 'line',
						data: args.data,
						options: {
							responsive: true,
							legend: {
								display: false,
								//display: true,
								position: 'bottom',
								labels:{
									boxWidth:5,
									fontSize:8,
									padding:8,
								}
							},
							scales: {
								xAxes:[{
									display: true,
									scaleLabel: {
										display: true,
										labelString: args.text.xAxes,
										fontSize: 14,
										fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif'
									}
								}],
								yAxes:[{
									display: true,
									scaleLabel: {
										display: false,
										labelString: args.text.yAxes
									}
								}]
							},
							hover: {
								mode: 'label'
							},
							tooltips: {
								custom: function(tooltip) {
								},
								callbacks: {
									title: function(tooltip){
										return args.data.dates[tooltip[0].index];
									}
								},
								mode: 'point'
							},
							events: ['click']
						}
					};
					$('#pvc_crono_leyend').empty();
					$.each( config.data.datasets, function ( i, dataset ){
						dataset.fill = args.design.fill;
						dataset.borderWidth = args.design.borderWidth;
						dataset.borderDash = args.design.borderDash;
						dataset.pointBorderWidth = args.design.pointBorderWidth;
						var itemList = $('<li data-id="'+dataset.post_id+'" title="['+dataset.post_id+']'+dataset.label+'"><span class="legendBullet" style="background:'+dataset.borderColor+';"></span>'+dataset.label+'</li>').click(function(e){
							$('#pvc_crono_post_type').val('custom');
							$('#pvc_crono_post_ids').show().append('['+$(this).data('id')+'],');
						});
						itemList.appendTo($('#pvc_crono_leyend'));
					});
					window.chartPVC = new Chart( document.getElementById('pvc_crono').getContext('2d'), config );
				}
			});
		}
	}

} )( jQuery );