define ->
	return ->
		window.scrollTo 0, 1 unless location.hash or pageYOffset
		return
