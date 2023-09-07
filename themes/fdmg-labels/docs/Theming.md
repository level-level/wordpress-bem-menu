# Themeing

The styling for the individual FDMG labels are implemented with a single class on the html tag.

| class on the `<html>` tag | Description                                             |
| ------------------------- | ------------------------------------------------------- |
| -                         | The default styling which is used for `Impact-investor` |
| `theme-energeia`          | The styling for `Energeia`                              |
| `theme-pensioen-pro`      | The styling for `Pensioen pro`                          |

## Color variables

```scss
:root {
	// Impact investor
	--primary: #cd3613;
	--primary-300: #df593b;

	.theme-energeia {
		--primary: #01b06d;
		--primary-300: #97d7b5;
	}

	.theme-pensioen-pro {
		--primary: #2c3b60;
		--primary-300: #5e7697;
	}
}
```

## Styling with TailwindCSS

There are theme specific classes available from the Tailwind config.
These can be combined with the dark variant as well. See example below.

```html
<div
	class="
		bg-primary
		dark:bg-primary-300
		theme-energeia:bg-[red]
		theme-energeia:dark:bg-[blue]
"
>
	<!-- bg-primary represents the default theme which is Impact Investor -->
	<!-- dark: represents the darkmode styling -->
	<!-- theme-energeia: and theme-pensioen-pro: represents specific themes -->
</div>
```
