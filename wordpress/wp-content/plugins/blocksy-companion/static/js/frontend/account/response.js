export const readAccountResponse = (response) => {
	const contentType = response.headers.get('content-type') || ''

	if (contentType.includes('application/json')) {
		return response.json().then((res) => ({
			rejected: !res.success,
			html: ''
		}))
	}

	return response.text().then((html) => ({
		rejected: false,
		html
	}))
}
