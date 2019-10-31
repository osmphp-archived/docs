import latinize from 'latinize';

export default function generateUrlKey(title) {
    return latinize(title)
        .toLocaleLowerCase()
        .replace(/\s/g, '_')
        .replace(/\W/g, '')
        .replace(/_/g, '-');
};