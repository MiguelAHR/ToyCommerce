import ctEvents from 'ct-events'

const VIDEO_INDICATOR_HTML = `<span class="ct-video-indicator">
	<svg width="40" height="40" viewBox="0 0 40 40" fill="#fff">
		<path class="ct-play-path" d="M20,0C8.9,0,0,8.9,0,20s8.9,20,20,20s20-9,20-20S31,0,20,0z M16,29V11l12,9L16,29z"/>

		<path class="ct-pause-path" d="M20 0C8.9 0 0 8.9 0 20s8.9 20 20 20 20-9 20-20S31 0 20 0zm-2.3 28h-4.6V12h4.6v16zm9.2 0h-4.6V12h4.6v16z"/>

		<path class="ct-video-loader" fill="currentColor" opacity="0.2" d="M20,11c-5,0-9,4-9,9c0,5,4,9,9,9s9-4,9-9C29,15,25,11,20,11z M20,27c-3.9,0-7-3.1-7-7c0-3.9,3.1-7,7-7s7,3.1,7,7C27,23.9,23.9,27,20,27z"/>

		<path class="ct-video-loader" fill="currentColor" d="M23.5,13.9l1-1.7C23.1,11.4,21.6,11,20,11v2C21.3,13,22.5,13.3,23.5,13.9z">
			<animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.6s" repeatCount="indefinite"/>
		</path>
	</svg>
</span>`

const getVideoState = (video) => {
	let playEvent = video.media_video_autoplay === 'yes' ? 'autoplay' : 'click'

	if ('media_video_event' in video) {
		playEvent = video.media_video_event
	}

	if (playEvent === 'click') {
		return null
	}

	if (playEvent !== 'hover') {
		return playEvent
	}

	if (video.media_video_hover_revert === 'no') {
		return playEvent
	}

	return `${playEvent}:revert`
}

const applyTo = (imgContainer, image) => {
	const video = image.blocksy_video
	const indicator = imgContainer.querySelector('.ct-video-indicator')
	const isPill = !!imgContainer.closest('.flexy-pills')

	const removePlayer = () => {
		;[...imgContainer.querySelectorAll('.ct-video-container')].map((el) =>
			el.remove()
		)
	}

	if (!video) {
		if (!indicator) {
			return false
		}

		removePlayer()
		indicator.remove()

		imgContainer.classList.remove('ct-simplified-player')
		imgContainer.removeAttribute('data-media-id')
		imgContainer.removeAttribute('data-state')

		return true
	}

	if (isPill) {
		if (indicator) {
			return false
		}

		imgContainer.insertAdjacentHTML('beforeend', VIDEO_INDICATOR_HTML)

		imgContainer.classList.toggle(
			'ct-simplified-player',
			video.media_video_player === 'yes'
		)

		return true
	}

	if (parseInt(imgContainer.dataset.mediaId) === parseInt(image.id)) {
		return false
	}

	removePlayer()

	if (!indicator) {
		imgContainer.insertAdjacentHTML('beforeend', VIDEO_INDICATOR_HTML)
	}

	imgContainer.classList.toggle(
		'ct-simplified-player',
		video.media_video_player === 'yes'
	)

	imgContainer.dataset.mediaId = image.id

	const state = getVideoState(video)

	if (state) {
		imgContainer.dataset.state = state
	} else {
		imgContainer.removeAttribute('data-state')
	}

	return true
}

export const syncVideoDecorations = ({ containers, image }) => {
	const hasChanged = containers
		.map((imgContainer) => applyTo(imgContainer, image))
		.some(Boolean)

	if (hasChanged) {
		ctEvents.trigger('blocksy:frontend:init')
	}
}
