# Image-Annotation

An image annotation plugin for WordPress that allows you to crop an area of the image and add notes to it.
This plugin is based on [Annotorious API](http://annotorious.github.io/api.html).

## [Demo Page](http://www.desmonding.com/image-annotation-plugin/)

## Installation

You can download the zip file and install it in WordPress Dashboard->Plugins->Add New->Upload Plugin.

## History

**Version 0.1.3** (09/20/2014):
- Add a plugin menu page for viewing all image notes

**Version 0.1.2** (09/13/2014):
- Add plugin settings page
	- Switch between two themes
	- Customize image selector (users can now specify the images they want to annotate)
- Fixed the bug that sometimes Annotorious fails to make images annotable
	- Only make an image annotable and load its annotations after the image is loaded

**Version 0.1.1** (09/12/2014):
- Resolve unxpected output warning message after plugin activation
- Correct position of edit textboxes (not showing right below the annotation in previous version)
- Load annotations for all images at start by default
- Add mouse enter/leave events for showing and hiding annotations

**Version 0.1** (08/27/2014):
- Implement basic functionalities of creating, editing and deleting image annotations
- Alpha version, so it does NOT support features like user info storage and comments integration
