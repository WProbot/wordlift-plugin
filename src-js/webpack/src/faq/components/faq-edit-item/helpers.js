/**
 * Helpers for the faq edit item.
 * @since 3.26.0
 * @author Naveen Muthusamy
 *
 */
/**
 * External dependencies.
 */
import React from "react";
/**
 * Internal dependencies.
 */
import { ANSWER_WORD_COUNT_WARNING_LIMIT, faqEditItemType } from "./index";

/**
 * Allowed html tags in the answer according to docs
 * Used to validate the html tags in the answer box.
 * https://developers.google.com/search/docs/data-types/faqpage#answer
 * @type {Array}
 */
const ANSWER_ALLOWED_HTML_TAGS = [
  "h1",
  "h2",
  "h3",
  "h4",
  "h5",
  "h6",
  "br",
  "ol",
  "ul",
  "li",
  "a",
  "p",
  "div",
  "b",
  "strong",
  "i",
  "em"
];

/**
 * Show the warning if the answer exceeds the word count limit.
 * @param type Question or Answer
 * @param textAreaValue
 * @return {*}
 */
export function showWarningIfAnswerWordCountExceedsLimit(type, textAreaValue) {
  if (type !== faqEditItemType.ANSWER || 0 === textAreaValue.length) {
    return <React.Fragment />;
  }
  const wordCount = textAreaValue.match(/\S+/g).length;
  if (wordCount <= ANSWER_WORD_COUNT_WARNING_LIMIT) {
    return <React.Fragment />;
  } else {
    return (
      <p className={"faq-edit-item__warning_text"}>
        <span className="dashicons dashicons-warning" /> Answer word count must not exceed
        {ANSWER_WORD_COUNT_WARNING_LIMIT} words
      </p>
    );
  }
}
function getAllInvalidTags(textAreaValue) {
  /**
   * This regex matches <p and </p ( so we detect invalid tags even if they have a incomplete closed tag in it)
   */
  const matches = textAreaValue.match(/<\/?[A-Za-z]+\s?/g).map(e =>
      e
          .replace("<", "")
          .replace("/", "")
          .toLowerCase()
          .replace(" ", "")
  );
  // Tags with no duplicate items.
  const tags = [...new Set(matches)];
  // Check which tags are not present in FAQ answer.
  let invalidTags = tags.filter(e => !ANSWER_ALLOWED_HTML_TAGS.includes(e));
  return invalidTags.map(e => {
    return "<" + e + ">";
  });
}

/**
 * Show alert if an invalid tag is present in the value.
 * @param type
 * @param textAreaValue
 * @return {*}
 */
export function showWarningIfInvalidHTMLTagPresentInAnswer(type, textAreaValue) {
  if (type !== faqEditItemType.ANSWER || 0 === textAreaValue.length) {
    return <React.Fragment />;
  }
  // Get all invalid tags in the answer.
  const invalidTags = getAllInvalidTags(textAreaValue)
  if (invalidTags.length === 0) {
    return <React.Fragment />;
  } else {
    // Return error message.
    const invalidTagsString = invalidTags.join(",");
    return (
      <p className={"faq-edit-item__danger_text"}>
        <span className="dashicons dashicons-no-alt" /> Invalid tags {invalidTagsString} is present in answer.
      </p>
    );
  }
}
