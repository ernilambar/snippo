import { createRoot } from "react-dom/client";
import { SnippetsApp, createWordpressApi } from "snippo-ui";

import "./snippets.css";

document.addEventListener("DOMContentLoaded", () => {
	const root = document.getElementById("snippo-snippets-app");

	if (root) {
		const api = createWordpressApi(SnippoObj.restUrl, SnippoObj.nonce);
		createRoot(root).render(
			<SnippetsApp
				api={api}
				options={{ layout: "sidebar", autoCopy: true }}
			/>,
		);
	}
});
