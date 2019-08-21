import Quill from 'quill';

let BlockEmbed = Quill.import('blots/block/embed');

class VideoBlot extends BlockEmbed {
    static create(value) {
        let node = super.create();

        node.setAttribute('contenteditable', false);

        let video = document.createElement('video');
        let source = document.createElement('source');
        video.setAttribute('controls', '');
        video.setAttribute('poster', value.thumbnail);
        video.style.display = 'block';
        video.style.width = '100%';
        source.setAttribute('src', value.url);
        source.setAttribute('type', value.mime);
        video.appendChild(source);
        node.appendChild(video);

        return node;
    }

    static value(node) {
        let source = node.querySelector('source');
        let video = node.querySelector('source');

        return {
            url: source.getAttribute('src'),
            mime: source.getAttribute('type'),
            thumbnail: video.getAttribute('poster'),
        };
    }
}

VideoBlot.tagName = 'div';
VideoBlot.blotName = 'video';
VideoBlot.className = 'embedded_video';

export default VideoBlot;
