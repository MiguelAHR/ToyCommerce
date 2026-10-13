import { readAccountResponse } from './response'

const responseWith = ({ contentType, json, text }) => ({
	headers: { get: () => contentType },
	json: () => Promise.resolve(json),
	text: () => Promise.resolve(text)
})

describe('readAccountResponse', () => {
	test('a JSON failure is reported as rejected', async () => {
		const result = await readAccountResponse(
			responseWith({
				contentType: 'application/json; charset=UTF-8',
				json: { success: false, data: [] }
			})
		)

		expect(result).toEqual({ rejected: true, html: '' })
	})

	test('an HTML response is passed through for the login_error/message scan', async () => {
		const html = '<div id="login_error">Bad email</div>'

		const result = await readAccountResponse(
			responseWith({
				contentType: 'text/html; charset=UTF-8',
				text: html
			})
		)

		expect(result).toEqual({ rejected: false, html })
	})

	test('a missing content type is treated as HTML', async () => {
		const result = await readAccountResponse(
			responseWith({ contentType: null, text: '<html></html>' })
		)

		expect(result.rejected).toBe(false)
	})
})
