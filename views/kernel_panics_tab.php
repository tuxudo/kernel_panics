<div id="kernel_panics-tab"></div>

<div id="lister" style="font-size: large; float: right;">
    <a href="/show/listing/kernel_panics/kernel_panics" title="List">
        <i class="btn btn-default tab-btn fa fa-list"></i>
    </a>
</div>
<h2 data-i18n="kernel_panics.kernel_panics"></h2>

<div id="kernel_panics-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
	// Create reusable modal function
	function showPanicModal(crashFile, fullText) {
		$('#myModal')
			.find('.modal-dialog').addClass('modal-lg').end()
			.find('.modal-title').text(crashFile).end()
			.find('.modal-body').html(fullText.replace(/\n/g, '<br>')).end()
			.find('button.ok')
				.text(i18n.t("dialog.close"))
				.off()
				.click(function(){ $('#myModal').modal('hide'); })
			.end()
			.modal('show');
	}

	// Define type configurations
	const typeConfig = {
		'GPU Reset': { icon: 'desktop', width: 600 },
		'iBridge Crash': { icon: 'link', width: 650 },
		'default': { icon: 'bomb', width: 1150 }
	};

	$.getJSON(appUrl + '/module/kernel_panics/get_tab_data/' + serialNumber)
		.done(function(data){
			if(!data || !data.length){
				$('#kernel_panics-msg').text(i18n.t('no_data'));
				$('#kernel_panics-cnt').text('');
				return;
			}
			
			$('#kernel_panics-msg').text('');
			$('#kernel_panics-count-view').removeClass('hide');
			$('#kernel_panics-cnt').text(data.length);

			const skipThese = ['id','serial_number','type','crash_file','full_text'];
			
			data.forEach(function(d){
				const config = typeConfig[d.type] || typeConfig.default;
				let rows = '';

				// Generate table rows
				for (const prop in d){
					if(skipThese.includes(prop)) continue;
					if(!d[prop]) continue;

					let content = d[prop];
					if(prop === 'date'){
						const date = new Date(d[prop] * 1000);
						content = `<span title="${moment(date).fromNow()}">${moment(date).format('llll')}</span>`;
					} else if(prop === 'extensions_backtrace'){
						content = d[prop].replace(/\n/g, '<br>').replace(/dependency:/g, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dependency:');
					}

					rows += `<tr><th style="width:150px;">${i18n.t('kernel_panics.'+prop)}</th><td>${content}</td></tr>`;
				}

				// Create panic entry
				$('<div>')
					.appendTo('#kernel_panics-tab')
					.append(
						$('<h4>')
							.append(`<i class="fa fa-${config.icon}"></i>  ${d.crash_file}&nbsp;&nbsp;`)
							.append(
								$('<button>')
									.addClass('btn btn-info btn-xs')
									.text(i18n.t('kernel_panics.view_panic'))
									.click(() => showPanicModal(d.crash_file, d.full_text))
							)
					)
					.append(
						$('<div>')
							.css('max-width', config.width)
							.append(
								$('<table>')
									.addClass('table table-striped table-condensed')
									.append($('<tbody>').html(rows))
							)
					);
			});
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			$('#kernel_panics-msg').text(i18n.t('error.loading'));
			console.error('Failed to load kernel panics:', textStatus, errorThrown);
		});
});
</script>
