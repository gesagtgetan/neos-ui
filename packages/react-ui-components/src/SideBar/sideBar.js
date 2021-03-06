import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import mergeClassNames from 'classnames';

class SideBar extends PureComponent {
    static propTypes = {
        /**
         * This prop controls the absolute positioning of the SideBar.
         */
        position: PropTypes.oneOf(['left', 'right']).isRequired,

        /**
         * An optional className to render on the div node.
         */
        className: PropTypes.string,

        /**
         * The children to render within the div node.
         */
        children: PropTypes.any.isRequired,

        /**
         * An optional css theme to be injected.
         */
        theme: PropTypes.shape({// eslint-disable-line quote-props
            'sideBar': PropTypes.string,
            'sideBar--left': PropTypes.string,
            'sideBar--right': PropTypes.string
        }).isRequired
    }

    render() {
        const {
            position,
            className,
            children,
            theme,
            ...rest
        } = this.props;
        const classNames = mergeClassNames({
            [className]: className && className.length,
            [theme.sideBar]: true,
            [theme['sideBar--left']]: position === 'left',
            [theme['sideBar--right']]: position === 'right'
        });

        return (
            <div {...rest} className={classNames}>
                {children}
            </div>
        );
    }
}

export default SideBar;
