import $ from 'jquery'
import Camera from '@/blocks/camera'

(function ($) {
  document.querySelectorAll('.block-camera').forEach(el => {
    new Camera(el)
  })
})($)
