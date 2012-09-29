define ['jquery', 'qtip'], ($) ->
	return (link, action, title) ->
		url = window.location.href.match /^([^\?]+\/)/
		return unless url
		url = url[1]

		jLink = $ link
		poolid = jLink.attr('href')
				.match /p=([0-9a-fA-F]+)/
		return unless poolid
		poolid = poolid[1]

		qtipParams =
			content:
				text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />'
				ajax:
					url: 'index.php'
					data:
						a: action
						p: poolid
						o: 'js'
					type: 'GET'
					once: false
				title:
					text: title
					button: true
			position:
				my: 'center'
				at: 'center'
				target: $ window
				effect: false
			show:
				event: 'click'
				solo: true
				modal: true
			hide: false
			style:
				classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded totePopup'
				def: false

		jLink.qtip qtipParams

		jLink.click ->
			return false

		return
