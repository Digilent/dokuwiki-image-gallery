/*Thumbnail element is passed in.  Use it to navigate up to the image-gallery then back down to the 
* gallery-large-image-link and gallery-large-image.  This allows multiple galleries on the same page.
*/
function thumbnailHover(element)
{	
	var link = element.parentElement.parentElement.children[0].children[0];
	var largeImage = link.children[0];
	link.setAttribute('href', element.src);
	largeImage.setAttribute('src', element.src);
}