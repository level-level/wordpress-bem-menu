import data from '../../fonts/selection.json';
const { icons, preferences } = data;
const { prefix } = preferences.fontPref;

const IconsList = [];
for ( const item of icons ) {
	IconsList.push( `${ prefix }${ item.properties.name }` );
}

export default IconsList;
