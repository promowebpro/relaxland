import assert from 'node:assert/strict';
import test from 'node:test';

class FakeClassList {
    constructor(element) {
        this.element = element;
    }

    toggle(name, enabled) {
        const names = new Set(this.element.className.split(/\s+/).filter(Boolean));
        enabled ? names.add(name) : names.delete(name);
        this.element.className = [...names].join(' ');
    }

    contains(name) {
        return this.element.className.split(/\s+/).includes(name);
    }
}

class FakeElement {
    constructor(tagName) {
        this.tagName = tagName.toUpperCase();
        this.attributes = new Map();
        this.children = [];
        this.dataset = {};
        this.listeners = new Map();
        this.className = '';
        this.classList = new FakeClassList(this);
        this.textContent = '';
        this.removed = false;
    }

    setAttribute(name, value) {
        this.attributes.set(name, String(value));
    }

    getAttribute(name) {
        return this.attributes.get(name) ?? null;
    }

    append(child) {
        this.children.push(child);
    }

    addEventListener(name, callback) {
        this.listeners.set(name, callback);
    }

    click() {
        this.listeners.get('click')?.();
    }

    remove() {
        this.removed = true;
    }
}

class FakeMap {
    static instances = [];

    constructor(container, props) {
        this.container = container;
        this.props = props;
        this.children = [];
        this.locations = [];
        this.zoom = props.location.zoom;
        this.destroyed = false;
        FakeMap.instances.push(this);
    }

    addChild(child) {
        this.children.push(child);
    }

    removeChild(child) {
        this.children = this.children.filter((candidate) => candidate !== child);
    }

    setLocation(location) {
        this.locations.push(location);
        if (location.zoom !== undefined) this.zoom = location.zoom;
    }

    update(props) {
        this.props = { ...this.props, ...props };
    }

    destroy() {
        this.destroyed = true;
    }
}

class FakeMarker {
    constructor(props, content) {
        this.props = props;
        this.content = content;
    }
}

class FakeLayer {
    constructor(props = {}) {
        this.props = props;
    }
}

const appendedScripts = [];

globalThis.document = {
    createElement: (tagName) => new FakeElement(tagName),
    head: {
        append: (element) => {
            appendedScripts.push(element);
            queueMicrotask(() => element.listeners.get('error')?.());
        },
    },
};
globalThis.window = {
    clearTimeout,
    matchMedia: () => ({ matches: false }),
    setTimeout,
    ymaps3: {
        ready: Promise.resolve(),
        YMap: FakeMap,
        YMapDefaultFeaturesLayer: FakeLayer,
        YMapDefaultSchemeLayer: FakeLayer,
        YMapMarker: FakeMarker,
    },
};

const { createYandexV3Adapter, loadYandexMaps } = await import('../../resources/js/genplan/map/yandex-v3-adapter.js');

test('Yandex v3 adapter manages controlled markers without duplicates', async () => {
    const selected = [];
    const places = [
        {
            slug: 'lake',
            name: 'Озеро',
            category: 'entertainment',
            category_label: 'Развлечения',
            category_symbol: 'Д',
            coordinates: { latitude: '55.5000000', longitude: '35.8000000' },
        },
        {
            slug: 'school',
            name: 'Школа',
            category: 'education',
            category_label: 'Образование',
            category_symbol: 'О',
            coordinates: { latitude: '55.5100000', longitude: '35.9000000' },
        },
    ];
    const adapter = await createYandexV3Adapter({
        container: new FakeElement('div'),
        config: {
            enabled: true,
            apiKey: 'test-only',
            sdkUrl: 'https://api-maps.yandex.ru/v3/',
            locale: 'ru_RU',
            defaultZoom: 11,
            minZoom: 6,
            maxZoom: 18,
            theme: 'light',
            timeoutMs: 1000,
        },
        settlement: { latitude: '55.5200000', longitude: '35.9500000' },
        places,
        onSelect: (slug) => selected.push(slug),
    });

    const map = FakeMap.instances.at(-1);
    const placeMarkers = () => map.children.filter((child) => child instanceof FakeMarker && child.content.dataset.mapPlace);

    assert.deepEqual(map.props.behaviors, ['drag', 'dblClick']);
    assert.equal(placeMarkers().length, 2);
    assert.equal(map.children.filter((child) => child instanceof FakeMarker).length, 3);

    placeMarkers()[0].content.click();
    assert.deepEqual(selected, ['lake']);

    adapter.selectMarker('school');
    const schoolMarker = placeMarkers().find((marker) => marker.content.dataset.mapPlace === 'school');
    assert.equal(schoolMarker.content.getAttribute('aria-pressed'), 'true');
    assert.equal(schoolMarker.content.classList.contains('is-selected'), true);

    adapter.updatePlaces([places[0]]);
    adapter.updatePlaces([places[0]]);
    assert.equal(placeMarkers().length, 1);

    adapter.fitBounds();
    assert.ok(map.locations.at(-1).bounds);
    const zoomBeforeControl = map.zoom;
    adapter.zoom(1);
    assert.equal(map.zoom, zoomBeforeControl + 1);

    adapter.destroy();
    assert.equal(map.destroyed, true);
    assert.equal(map.children.filter((child) => child instanceof FakeMarker).length, 0);
});

test('failed provider scripts are removed before another attempt', async () => {
    window.ymaps3 = null;
    const config = {
        enabled: true,
        apiKey: 'test-only',
        sdkUrl: 'https://api-maps.yandex.ru/v3/',
        locale: 'ru_RU',
        timeoutMs: 1000,
    };

    await assert.rejects(loadYandexMaps(config), { code: 'provider_unavailable' });
    await assert.rejects(loadYandexMaps(config), { code: 'provider_unavailable' });

    assert.equal(appendedScripts.length, 2);
    assert.ok(appendedScripts.every((script) => script.removed));
});
