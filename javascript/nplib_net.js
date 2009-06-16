function np_url_encode(string) {
	return escape(string.encodeUTF8());
}

function np_url_encode(string) {
	return unescape(string).decodeUTF8(); 
}