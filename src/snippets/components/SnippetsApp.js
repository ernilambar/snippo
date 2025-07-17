import { useState, useEffect } from "react";
import apiFetch from "@wordpress/api-fetch";
import "../snippets.css";

const SnippetsApp = () => {
	const [snippets, setSnippets] = useState({});
	const [selected, setSelected] = useState("");
	const [fields, setFields] = useState([]);
	const [form, setForm] = useState({});
	const [output, setOutput] = useState("");
	const [error, setError] = useState("");
	const [copied, setCopied] = useState(false);

	useEffect(() => {
		apiFetch({
			path: SnippoObj.restUrl,
			headers: { "X-WP-Nonce": SnippoObj.nonce },
		}).then(setSnippets);
	}, []);

	useEffect(() => {
		if (selected && snippets[selected]) {
			setFields(snippets[selected].fields || []);
			setForm({});
			setOutput("");
			setError("");
			setCopied(false);
			// If there are no fields, auto-generate the snippet output
			if (
				!snippets[selected].fields ||
				snippets[selected].fields.length === 0
			) {
				apiFetch({
					path: SnippoObj.restUrlRender,
					method: "POST",
					headers: { "X-WP-Nonce": SnippoObj.nonce },
					data: { key: selected, data: {} },
				})
					.then((res) => setOutput(res.output))
					.catch((err) =>
						setError(err.message || "Error rendering snippet"),
					);
			}
		}
	}, [selected, snippets]);

	const handleChange = (field, value) => {
		setForm({ ...form, [field]: value });
	};

	const handleSubmit = (e) => {
		e.preventDefault();
		setError("");
		setOutput("");
		setCopied(false);
		apiFetch({
			path: SnippoObj.restUrlRender,
			method: "POST",
			headers: { "X-WP-Nonce": SnippoObj.nonce },
			data: { key: selected, data: form },
		})
			.then((res) => setOutput(res.output))
			.catch((err) => setError(err.message || "Error rendering snippet"));
	};

	const handleCopy = () => {
		if (output) {
			navigator.clipboard.writeText(output).then(() => {
				setCopied(true);
				setTimeout(() => setCopied(false), 1500);
			});
		}
	};

	// Helper to check if all required fields are filled
	const allRequiredFilled = fields.every(
		(field) =>
			!field.required ||
			(form[field.name] && form[field.name].toString().trim() !== ""),
	);

	return (
		<div>
			<select
				value={selected}
				onChange={(e) => setSelected(e.target.value)}
			>
				<option value="">Select a snippet</option>
				{Object.keys(snippets).map((key) => {
					const snippet = snippets[key];
					const title =
						snippet.meta && snippet.meta.title
							? snippet.meta.title
							: key;
					return (
						<option key={key} value={key}>
							{title}
						</option>
					);
				})}
			</select>
			{fields.length > 0 && (
				<form onSubmit={handleSubmit} className="snippetsapp-form">
					{fields.map((field) => (
						<div key={field.name} className="snippetsapp-field">
							<label className="snippetsapp-label">
								{field.label || field.name}
							</label>
							<input
								type="text"
								value={form[field.name] || ""}
								onChange={(e) =>
									handleChange(field.name, e.target.value)
								}
								required={field.required}
								className="snippetsapp-input"
							/>
						</div>
					))}
					<button
						type="submit"
						className="button button-primary snippetsapp-submit-button"
						disabled={!allRequiredFilled}
					>
						Generate
					</button>
				</form>
			)}
			{output && (
				<div className="snippetsapp-output-container">
					<div className="snippetsapp-output-content">
						<div
							className="snippetsapp-output-text"
							dangerouslySetInnerHTML={{ __html: output }}
						/>
						<button
							type="button"
							onClick={handleCopy}
							className="snippetsapp-copy-button"
						>
							{copied ? "Copied!" : "Copy Snippet"}
						</button>
					</div>
				</div>
			)}
			{error && <div className="snippetsapp-error-message">{error}</div>}
		</div>
	);
};

export default SnippetsApp;
