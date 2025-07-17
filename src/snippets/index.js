import { createRoot } from "react-dom/client";
import SnippetsApp from "./components/SnippetsApp";

document.addEventListener("DOMContentLoaded", () => {
	const root = document.getElementById("snippo-snippets-app");
	if (root) {
		createRoot(root).render(<SnippetsApp />);
	}
});
