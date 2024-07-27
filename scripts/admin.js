const ajaxUrl = ajaxData.ajaxUrl

document.addEventListener( 'DOMContentLoaded', () => {
	'use strict'

	fetchPosts()
} )

/**
 * Custom AJAX request.
 *
 * @param	{Object}	formData	Data for fetch body.
 * @param	{Object}	args		Object of additional fetch settings.
 * @returns	{Array}					Response data array.
 */
const customAjaxRequest = async ( formData = {}, args = {} ) => {
	let response = await fetch( ajaxUrl, {
		method	: 'post',
		body	: formData,
		...args
	} )

	return await response.json()
}

const fetchPosts = () => {
	const form = document.querySelector( '.testplugin-form' )

	if( ! form ) return

	form.addEventListener( 'submit', e => {
		e.preventDefault()

		const formData = new FormData()

		formData.append( 'action', 'testplugin_ajax_load_todos' )
		customAjaxRequest( formData ).then( res => {
			if( res ){
				switch( res.success ){
					case true:
						console.log( res.data.msg )
						break

					default:
						console.error( res.data.msg )
				}
			}
		} )
	} )
}