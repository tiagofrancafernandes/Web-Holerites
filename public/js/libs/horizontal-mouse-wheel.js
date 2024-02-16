window.horizontalMouseWheel = (
    slideContainerElement,
    incrementBy = 50,
    readChildWheel = false
) => {
    if (!slideContainerElement || !slideContainerElement.addEventListener) {
        console.error('Invalid "slideContainerElement"', slideContainerElement);
        return;
    }

    incrementBy = isNaN(parseInt(incrementBy)) ? 50 : parseInt(incrementBy);

    const setScrollVal = (slideContainerElement, value) => {
        if (!slideContainerElement) {
            return;
        }

        value = isNaN(parseInt(value)) ? 0 : parseInt(value);

        slideContainerElement.setAttribute('data-scroll-val', value);
    };

    const getScrollVal = (slideContainerElement) => {
        if (!slideContainerElement || !slideContainerElement.dataset) {
            return;
        }

        let scrollVal = parseInt(slideContainerElement.dataset.scrollVal);

        return isNaN(scrollVal) ? 0 : scrollVal;
    };

    function hScrollTo(slideContainerElement, scrolled) {
        if (!slideContainerElement) {
            return;
        }

        // slideContainerElement.style.setProperty('transform', 'translateX(' + scrolled + 'px');
        slideContainerElement.scrollLeft = scrolled;
    }

    // document.querySelector(".fi-tabs.overflow-x-auto").addEventListener('wheel', (event) => console.log(event));

    const peformScroll = (event = null, element = null) => {
        if (!event) {
            console.error('Invalid event', event);
            return;
        }

        // document.querySelector('.code').innerHTML = window.scrollTop();
        let slider = element || event?.target || slideContainerElement;

        let deltaY = event?.deltaY || incrementBy;
        let isUp = (deltaY > 0);
        let isDown = !isUp;

        if (!slider) {
            return;
        }

        let newValue = (getScrollVal && getScrollVal(slider) >= 0 ? getScrollVal(slider) : 0) + (
            isUp ? incrementBy : - incrementBy
        );

        // let maxValue = slider.scrollWidth - (slider.querySelector(".fi-tabs-item")?.scrollWidth || 0);
        let maxValue = slider.scrollLeftMax;

        newValue = newValue >= maxValue ? maxValue : newValue;

        setScrollVal(slider, parseInt(newValue));

        // slider && slider.style.setProperty('transform','translateX(' + newValue + 'px');
        // slider.scrollLeft = newValue
        hScrollTo(slider, newValue);

        event && event.preventDefault();
    };

    if (!slideContainerElement.classList.contains('hmm-started')) {
        slideContainerElement.classList.add('hmm-started');
        slideContainerElement
            && slideContainerElement.addEventListener('wheel', event => peformScroll(event, slideContainerElement));
    }

    readChildWheel && Object.entries(slideContainerElement.children).forEach(children => {
        let childNode = children[1];

        if (!childNode || !childNode.tagName) {
            return;
        }

        if (!childNode.classList.contains('hmm-started')) {
            childNode.classList.add('hmm-started');
            childNode.addEventListener('wheel', event => peformScroll(event, slideContainerElement));
        }
    });
}

window.initHMM = (incrementBy = 50, readChildWheel = false) => {
    if (!window.horizontalMouseWheel) {
        return;
    }

    document.querySelectorAll('.fi-tabs.overflow-x-auto, [data-slide-load="horizontalMouseWheel"]')
        ?.forEach(element => {
            window.horizontalMouseWheel(element, incrementBy, readChildWheel);
        });
};

window.init_horizontalMouseWheel = () => {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.horizontalMouseWheel) {
            return;
        }

        window.initHMM && window.initHMM(50, true);
    });
};

document.addEventListener('DOMContentLoaded', (event) => {
    window.addEventListener('livewire:navigated', event => {
        console.log('livewire:navigated', /* event */);
        window.init_horizontalMouseWheel();
    });
});

window.addEventListener('wheel', event => {
    window.initHMM(50, true);
    let targetParent = event.target.parentElement;
    let targetParentClassList = targetParent?.classList || null;

    if (!targetParentClassList) {
        return;
    }

    if (targetParentClassList.contains('hmm-started')) {
        return;
    }

    if (
        targetParentClassList.contains('fi-tabs')
        && targetParentClassList.contains('overflow-x-auto')
    ) {
        // event.target.classList.add('hmm-started');

        if (!targetParent) {
            return;
        }

        // targetParent.classList.add('hmm-started');
        window.horizontalMouseWheel(targetParent, 50, true);
    }
});
