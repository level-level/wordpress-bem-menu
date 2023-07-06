# Themeing

The styling for the individual FDMG labels are implemented with a single class on the html tag.

| class on the `<html>` tag | Description                                             |
| ------------------------- | ------------------------------------------------------- |
| -                         | The default styling which is used for `Impact-investor` |
| `theme-energeia`          | The styling for `Energeia`                              |
| `theme-pp`                | The styling for `Pensioen pro`                          |

## Color variables

```scss
:root {
	// Impact investor
	--primary-400: #cd3613;
	--primary-300: #df593b;

	// Energeia
	.theme-energeia {
		--primary-400: #01b06d;
		--primary-300: #97d7b5;
	}

	// Pensioen Pro
	.theme-pp {
		--primary-400: #2c3b60;
		--primary-300: #5e7697;
	}
}
```

## Styling on component level

If styling needs to be adjusted on component level, there are a few mixins to use:

```scss
.block {
	background-color: red; // impact-investor

	@include dark-mode {
		background-color: darkred; // impact-investor:dark mode
	}

	@include theme-energeia {
		background-color: green; // energeia
	}

	@include theme-energeia(true) {
		background-color: darkgreen; // energeia:dark mode
	}

	@include theme-pp {
		background-color: blue; // pensioen pro
	}

	@include theme-pp(true) {
		background-color: darkblue; // pensioen pro:dark mode
	}
}
```
